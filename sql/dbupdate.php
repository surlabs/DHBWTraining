<#1>
<?php
//Previous Version
global $DIC;

$db = $DIC->database();
if (!$db->tableExists('xdht_config')) {
    $fields = [
        'name' => [
            'type' => 'text',
            'length' => 250,
            'notnull' => true
        ],
        'value' => [
            'type' => 'text',
            'length' => 4000,
            'notnull' => false
        ]
    ];

    $db->createTable('xdht_config', $fields);
    $db->addPrimaryKey('xdht_config', ['name']);

    $db->insert('xdht_config', ['name' => 'salt', 'value' => \platform\DHBWTrainingConfig::generateSalt()]);
}

if (!$db->tableExists('rep_robj_xdht_settings')) {
    $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ],
        'dhbw_training_object_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'question_pool_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false
        ],
        'is_online' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ],
        'installation_key' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ],
        'secret' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ],
        'url' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ],
        'log' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ],
        'recommender_system_server' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ],
        'rec_sys_ser_bui_in_deb_comp' => [
            'type' => 'text',
            'notnull' => true
        ],
        'rec_sys_ser_bui_in_deb_progm' => [
            'type' => 'text',
            'notnull' => true
        ],
        'learning_progress' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ]
    ];

    $db->createTable('rep_robj_xdht_settings', $fields);
    $db->addPrimaryKey('rep_robj_xdht_settings', ['id']);
    $db->createSequence('rep_robj_xdht_settings');
}

if (!$db->tableExists('rep_robj_xdht_partic')) {
    $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ],
        'training_obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'usr_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'status' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => true
        ],
        'created' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'updated_status' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'last_access' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'created_usr_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'updated_usr_id' => [
            'type' => 'integer',
            'length' => 8,
            'notnull' => false
        ],
        'full_name' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => false
        ]
    ];

    $db->createTable('rep_robj_xdht_partic', $fields);
    $db->addPrimaryKey('rep_robj_xdht_partic', ['id']);
    $db->createSequence('rep_robj_xdht_partic');
}

$db->modifyTableColumn('copg_pobj_def', 'component', ['length' => 120]);
?>
<#2>
<?php
//Previous Version
?>
<#3>
<?php
//Previous Version
?>
<#4>
<?php
//Previous Version
?>
<#5>
<?php
//Previous Version
?>
<#6>
<?php
//Previous Version
?>
<#7>
<?php
//Previous Version
?>
<#8>
<?php
//Previous Version
?>
<#9>
<?php
//Previous Version
?>
<#10>
<?php
global $DIC;
$db = $DIC->database();

if ($db->tableExists('rep_robj_xdht_settings')) {
    if ($db->sequenceExists('rep_robj_xdht_settings') ) {
        $db->dropSequence('rep_robj_xdht_settings');
    }

    if ($db->tableColumnExists('rep_robj_xdht_settings', 'id')) {
        $db->dropPrimaryKey('rep_robj_xdht_settings');
        $db->dropTableColumn('rep_robj_xdht_settings', 'id');
        $db->addPrimaryKey('rep_robj_xdht_settings', ['dhbw_training_object_id']);
    }
}
?>
<#11>
<?php
global $DIC;
$db = $DIC->database();

if ($db->tableExists('rep_robj_xdht_partic')) {
    $db->modifyTableColumn('rep_robj_xdht_partic', 'full_name', ['length' => 255]);
}
?>
