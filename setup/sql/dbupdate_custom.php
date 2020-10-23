<#1>
<?php
if (!$ilDB->tableColumnExists('event', 'show_cannot_part')) {
    $ilDB->addTableColumn(
        'event',
        'show_cannot_part',
        [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]
    );
}



?>