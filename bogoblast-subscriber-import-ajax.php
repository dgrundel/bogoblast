<?php
    $post_data = array(
        'uploaded_file_path' => $_POST['uploaded_file_path'],
        'header_row' => $_POST['header_row'],
        'limit' => $_POST['limit'],
        'offset' => $_POST['offset'],
        'import_row' => maybe_unserialize(stripslashes($_POST['import_row'])),
        'map_to' => maybe_unserialize(stripslashes($_POST['map_to'])),
        'bogoblast_business_id' => maybe_unserialize(stripslashes($_POST['bogoblast_business_id']))
    );
    
    //var_dump($post_data);
    //var_dump($_POST['custom_field_name']);
    
    if(isset($post_data['uploaded_file_path'])) {
        
        $error_messages = array();
        
        //now that we have the file, grab contents
        $temp_file_path = $post_data['uploaded_file_path'];
        $handle = fopen( $temp_file_path, 'r' );
        $import_data = array();
        
        if ( $handle !== FALSE ) {
            while ( ( $line = fgetcsv($handle) ) !== FALSE ) {
                $import_data[] = $line;
            }
            fclose( $handle );
        } else {
            $error_messages[] = 'Could not open CSV file.';
        }
        
        if(sizeof($import_data) == 0) {
            $error_messages[] = 'No data found in CSV file.';
        }
        
        //discard header row from data set, if we have one
        if(intval($post_data['header_row']) == 1) array_shift($import_data);
        
        //total size of data to import (not just what we're doing on this pass)
        $row_count = sizeof($import_data);
        
        //slice down our data based on limit and offset params
        $limit = intval($post_data['limit']);
        $offset = intval($post_data['offset']);
        if($limit > 0 || $offset > 0) {
            $import_data = array_slice($import_data, $offset , ($limit > 0 ? $limit : null), true);
        }
        
        //a few stats about the current operation to send back to the browser.
        $rows_remaining = ($row_count - ($offset + $limit)) > 0 ? ($row_count - ($offset + $limit)) : 0;
        $insert_count = ($row_count - $rows_remaining);
        $insert_percent = number_format(($insert_count / $row_count) * 100, 1);
        
        //array that will be sent back to the browser with info about what we inserted.
        $inserted_rows = array();
        
        //this is where the fun begins
        foreach($import_data as $row_id => $row) {
            
            //unset new_post_id
            $new_post_id = null;
            
            //set some initial post values
            $new_post = array();
            $new_post['post_type'] = 'bogoblast_subscriber';
            $new_post['post_status'] = 'publish';
            $new_post['post_title'] = '';
            $new_post['post_content'] = '';
            
            //set some initial post_meta values
            $new_post_meta = array();
            
            $new_post_business_ids = array();
            
            //keep track of any errors or messages generated during post insert or image downloads.
            $new_post_errors = array();
            $new_post_messages = array();
            
            //track whether or not the post was actually inserted.
            $new_post_insert_success = false;
            
            foreach($row as $key => $col) {
                $map_to = $post_data['map_to'][$key];
                
                //skip if the column is blank.
                //useful when two CSV cols are mapped to the same product field.
                //you would do this to merge two columns in your CSV into one product field.
                if(strlen($col) == 0) {
                    continue;
                }
                
                //prepare the col value for insertion into the database
                switch($map_to) {
                    case 'post_title':
                        $new_post[$map_to] = $col;
                        break;
                    
                    case 'bogoblast_business_by_name':
                        $business_names = explode('|', $col);
                        foreach($business_names as $business_name) {
                            $business = get_page_by_title($business_name, 'OBJECT', 'bogoblast_business');
                            if($business !== null) {
                                $new_post_business_ids[] = $business->ID;
                                
                            } else {
                                $new_business = array();
                                $new_business['post_type'] = 'bogoblast_business';
                                $new_business['post_title'] = $business_name;
                                $new_business['post_content'] = '';
                                $new_business['post_status'] = 'publish';
                                
                                $new_business_id = wp_insert_post($new_business, true);
                                
                                if(is_wp_error($new_business_id)) {
                                    $new_post_errors[] = 'Couldn\'t insert business with name "'.$business_name.'".';
                                } else {
                                    $new_post_messages[] = 'Inserted new business "'.$business_name.'" with ID "'.$new_business_id.'".';
                                    $new_post_business_ids[] = $new_business_id;
                                }
                            }
                        }
                        break;
                        
                    case 'bogoblast_business_by_id':
                        $business_ids = explode('|', $col);
                        foreach($business_ids as $business_id) {
                            $business = get_post($business_id, 'OBJECT');
                            if($business !== null) {
                                $new_post_business_ids[] = $business->ID;
                            } else {
                                $new_post_errors[] = 'Couldn\'t find business with ID "'.$business_id.'".';
                            }
                        }
                        
                        break;
                }
            }
            
            //try to find a subscriber with a matching e-mail address (post_title)
            $existing_post = get_page_by_title($new_post['post_title'], 'OBJECT', 'bogoblast_subscriber');
            
            if(strlen($new_post['post_title']) > 0 || $existing_post !== null) {
                
                //insert/update product
                if($existing_post !== null) {
                    $new_post_messages[] = 'Updating subscriber with ID '.$existing_post->ID.'.';
                    
                    $new_post['ID'] = $existing_post->ID;
                    $new_post_id = wp_update_post($new_post);
                } else {
                    $new_post_id = wp_insert_post($new_post, true);
                }
                
                if(is_wp_error($new_post_id)) {
                    $new_post_errors[] = 'Couldn\'t insert subscriber with email "'.$new_post['post_title'].'".';
                } elseif($new_post_id == 0) {
                    $new_post_errors[] = 'Couldn\'t update subscriber with ID "'.$new_post['ID'].'".';
                } else {
                    //insert successful!
                    $new_post_insert_success = true;
                    
                    //business id selected during the upload process
                    $bogoblast_business_id = intval($post_data['bogoblast_business_id']);
                    if($bogoblast_business_id != 0) $new_post_business_ids[] = $bogoblast_business_id;
                    
                    //business ids present in the CSV
                    foreach($new_post_business_ids as $business_id) {
                        //have to delete first to make sure we're not adding a duplicate
                        delete_post_meta($new_post_id, '_bogoblast_business_id', $business_id);
                        add_post_meta($new_post_id, '_bogoblast_business_id', $business_id, false);
                    }
                }
                
            } else {
                $new_post_errors[] = 'Skipped import of subscriber without an e-mail address';
            }
            
            //this is returned back to the results page.
            //any fields that should show up in results should be added to this array.
            $inserted_rows[] = array(
                'row_id' => $row_id,
                'post_id' => $new_post_id ? $new_post_id : '',
                'email' => $new_post['post_title'] ? $new_post['post_title'] : '',
                'businesses' => implode(', ', $new_post_business_ids),
                'has_errors' => (sizeof($new_post_errors) > 0),
                'errors' => $new_post_errors,
                'has_messages' => (sizeof($new_post_messages) > 0),
                'messages' => $new_post_messages,
                'success' => $new_post_insert_success
            );
        }
    }
    
    echo json_encode(array(
        'remaining_count' => $rows_remaining,
        'row_count' => $row_count,
        'insert_count' => $insert_count,
        'insert_percent' => $insert_percent,
        'inserted_rows' => $inserted_rows,
        'error_messages' => $error_messages,
        'limit' => $limit,
        'new_offset' => ($limit + $offset)
    ));