<?php
/**
* @category   Entrepids
* @package    Entrepids_Cronscheduler
* @author     miguel.perez@entrepids.com
* @website    http://www.entrepids.com
*/

$installer = $this;

$installer->startSetup();

/**
 * Create table 'ht_cron_schedule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('ht_cron_schedule'))
    ->addColumn('schedule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Schedule Id')
    ->addColumn('job_code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Job Code')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 7, array(
        'nullable'  => false,
        'default'   => 'pending',
        ), 'Status')
    ->addColumn('messages', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Messages')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Created At')
    ->addColumn('scheduled_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Scheduled At')
    ->addColumn('executed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Executed At')
    ->addColumn('finished_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Finished At')
    ->addColumn('parameters', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => true,
        ), 'Serialized Parameters')
    ->addColumn('eta', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Estimated Time of Arrival')
    ->addColumn('host', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Host runnig this job')
    ->addColumn('pid', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Process id of this job')
    ->addColumn('progress_message', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => true,
        ), 'Progress message')
    ->addColumn('last_seen', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Last seen')
    ->addColumn('kill_request', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Kill Request')
    ->addColumn('scheduled_by', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable'  => true,
        'default'  => null
        ), 'Scheduled by')
    ->addColumn('scheduled_reason', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => true,
        'default'  => null
        ), 'Scheduled Reason')
    ->addColumn('memory_usage', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'unsigned' => true,
        'nullable' => true,
        'default'  => null,
        ), 'Memory Used in MB')
    ->addIndex($installer->getIdxName('ht_cron_schedule', array('job_code')),
        array('job_code'))
    ->addIndex($installer->getIdxName('ht_cron_schedule', array('scheduled_at', 'status')),
        array('scheduled_at', 'status'))
    ->setComment('Cron Schedule Historic Table');

$installer->getConnection()->createTable($table);

$installer->endSetup();
