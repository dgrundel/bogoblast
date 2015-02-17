<style type="text/css">
    .bogoblast_business_merge_wrapper form { padding: 20px 0; }
    
    .bogoblast_business_merge_wrapper .import_error_messages {
        margin: 6px 0;
        padding: 0;
    }
    
    .bogoblast_business_merge_wrapper .import_error_messages li {
        margin: 2px 0;
        padding: 4px;
        background-color: #f9dede;
        border: 1px solid #ff8e8e;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
    }
    
    .bogoblast_business_merge_wrapper #import_status {
        padding: 8px 8px 8px 82px;
        min-height: 66px;
        position: relative;
        margin: 6px 0;
        background-color: #fff5d1;
        border: 1px solid #ffc658;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
    }
    .bogoblast_business_merge_wrapper #import_status.complete {
        background-color: #ecfdbe;
        border: 1px solid #a1dd00;
    }
    
    .bogoblast_business_merge_wrapper #import_status img {
        position: absolute;
        top: 8px;
        left: 8px;
    }
    
    .bogoblast_business_merge_wrapper #import_status strong {
        font-size: 18px;
        line-height: 1.2em;
        padding: 6px 0;
        display: block;
    }
    
    .bogoblast_business_merge_wrapper #import_status #import_in_progress { display: block; }
    .bogoblast_business_merge_wrapper #import_status.complete #import_in_progress { display: none; }
    
    .bogoblast_business_merge_wrapper #import_status #import_complete { display: none; }
    .bogoblast_business_merge_wrapper #import_status.complete #import_complete { display: block; }
    
    .bogoblast_business_merge_wrapper #import_status td,
    .bogoblast_business_merge_wrapper #import_status th {
        text-align: left;
        font-size: 13px;
        line-height: 1em;
        padding: 4px 10px 4px 0;
    }
    
    .bogoblast_business_merge_wrapper table th { vertical-align: top; }
    
    .bogoblast_business_merge_wrapper table th.narrow,
    .bogoblast_business_merge_wrapper table td.narrow { width: 65px; }
    .bogoblast_business_merge_wrapper table input { margin: 1px 0; }
    
    .bogoblast_business_merge_wrapper table tr.header_row th {
        background-color: #DCEEF8;
        background-image: none;
        vertical-align: middle;
    }
    
    .bogoblast_business_merge_wrapper .map_to_settings {
        margin: 2px 0;
        padding: 2px;
        overflow: hidden;
    }
    
    .bogoblast_business_merge_wrapper .field_settings {
        display: none;
        margin: 2px 0;
        padding: 4px;
        background-color: #e0e0e0;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }
    .bogoblast_business_merge_wrapper .field_settings h4 {
        margin: 0;
        font-size: 0.9em;
        line-height: 1.2em;
    }
    .bogoblast_business_merge_wrapper .field_settings p {
        margin: 4px 0;
        overflow: hidden;
        font-size: .9em;
        line-height: 1.3em;
    }
    .bogoblast_business_merge_wrapper .field_settings input[type="text"] { width: 98%; }
    
    .bogoblast_business_merge_wrapper #inserted_rows tr.error td { background-color: #FFF6D3; }
    .bogoblast_business_merge_wrapper #inserted_rows tr.fail td { background-color: #FFA8A8; }
    
    .bogoblast_business_merge_wrapper #inserted_rows .icon {
        display: block;
        width: 16px;
        height: 16px;
        background-position: 0 0;
        background-repeat: no-repeat;
    }
    .bogoblast_business_merge_wrapper #inserted_rows tr.success .icon { background-image: url('<?php echo BOGOBLAST_URL; ?>img/accept.png'); }
    .bogoblast_business_merge_wrapper #inserted_rows tr.error .icon { background-image: url('<?php echo BOGOBLAST_URL; ?>img/error.png'); }
    .bogoblast_business_merge_wrapper #inserted_rows tr.fail .icon { background-image: url('<?php echo BOGOBLAST_URL; ?>img/exclamation.png'); }
    
    .bogoblast_business_merge_wrapper #debug {
        display: none;
        font-family: monospace;
        font-size: 14px;
        line-height: 16px;
        color: #333;
        background-color: #f5f5f5;
        border: 1px solid #efefef;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        padding: 0 10px;
    }
</style>