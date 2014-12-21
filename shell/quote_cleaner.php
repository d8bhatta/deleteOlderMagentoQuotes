<?php
/**
 * Created by PhpStorm.
 * User: Deepak Bhatta
 * Date: 12/21/14
 * Time: 10:03 AM
 */


require_once 'abstract.php';

class quote_cleaner extends Mage_Shell_Abstract
{
    /**
     * Run script
     */
    public function run()
    {
        echo "Quotes cleaning process starts\r\n";
        $quoteDeleteLimit = null;
        $limit = ( (int) $this->getArg('limit') <= 0) ? 50000: (int) $this->getArg('limit') ;
        $regQuotesDuration = ((int)$this->getArg('regQuotes') <= 0) ? 60: (int)$this->getArg('regQuotes');
        $anonQuotesDuration = ((int)$this->getArg('anonQuotes') <= 0 ) ? 30 : (int)$this->getArg('anonQuotes');
        if (($limit > 0  && $limit <=100000)) {
             $this->_cleanOlderQuotes($limit , $regQuotesDuration , $anonQuotesDuration);
        }
        else {
            echo "Please enter limit value less than 100000.";
            exit;
        }
        echo "Quotes cleaning process finished\r\n";
    }


    protected  function _cleanOlderQuotes($limit = 100000,  $registeredOlderQuotes = 60 , $anonymousOlderQuotes = 30 ) {
        $quoteReport = array();
        /* @var $writeConnection Varien_Db_Adapter_Pdo_Mysql */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableName = Mage::getSingleton('core/resource')->getTableName('sales/quote');
        $tableName = $writeConnection->quoteIdentifier($tableName, true);
        $regQuoteSql =  sprintf('select count(*)  FROM %s WHERE (NOT ISNULL(customer_id) AND customer_id != 0) AND updated_at < DATE_SUB(Now(), INTERVAL %s DAY) ',
            $tableName,
            $registeredOlderQuotes
        );

        $totalRegQuotes = $writeConnection->fetchOne($regQuoteSql);
        $numberOfIterations = ceil($totalRegQuotes / $limit);
         Mage::log("Total number of Registered customer quotes to be deleted = " . $totalRegQuotes);
        for($i=1;$i<=$numberOfIterations;$i++) {
            $startTime = time();
            $sql = sprintf('DELETE FROM %s WHERE (NOT ISNULL(customer_id) AND customer_id != 0) AND updated_at < DATE_SUB(Now(), INTERVAL %s DAY) LIMIT %s',
                $tableName,
                $registeredOlderQuotes,
                $limit
            );
            $result = $writeConnection->query($sql);
            $quoteReport['customer']['count'][$i] = $result->rowCount();
            $quoteReport['customer']['duration'][$i] = time() - $startTime;
            Mage::log('[CLEANING QUOTE DATA] Cleaning old registered customer quotes (duration: '.$quoteReport['customer']['duration'][$i].', row count: '.$quoteReport['customer']['count'][$i].')');
        }



        $selectAnonymousSql =  sprintf('select count(*)  FROM %s WHERE (ISNULL(customer_id) OR customer_id = 0) AND updated_at < DATE_SUB(Now(), INTERVAL %s DAY)',
            $tableName,
            $anonymousOlderQuotes
        );

        $totalAnonQuotes = $writeConnection->fetchOne($selectAnonymousSql);
        Mage::log("Total number of Anonymous customer quotes to be deleted = " . $totalAnonQuotes);

        $numberOfIterations = ceil($totalAnonQuotes / $limit);
        for($i=1;$i<=$numberOfIterations;$i++) {
            $sql = sprintf('DELETE FROM %s WHERE (ISNULL(customer_id) OR customer_id = 0) AND updated_at < DATE_SUB(Now(), INTERVAL %s DAY) LIMIT %s',
                $tableName,
                $anonymousOlderQuotes,
                $limit
            );
            $result = $writeConnection->query($sql);
            $quoteReport['anonymous']['count'][$i] = $result->rowCount();
            $quoteReport['anonymous']['duration'][$i] = time() - $startTime;
            Mage::log('[CLEANING QUOTE DATA] Cleaning old anonymous quotes (duration: '.$quoteReport['anonymous']['duration'][$i].', row count: '.$quoteReport['anonymous']['count'][$i].')');
        }

        return $quoteReport;
    }

        /**
         * Retrieve Usage Help Message
         */
        public function usageHelp()
        {
            return <<<USAGE
      Usage:  php -f aoe_quotecleaner.php -- [options]
      --limit <delete_limit>        Delete quote limit (max 100.000)
      --regQuotes <reg_quotes>      How older registered quotes you want to delete (in days) , normally 60 days.
       --anonQuotes <anon_quotes>    How older anonymous  quotes you want to delete (in days), normally 30 days.
      help                          This help
USAGE;
        }
}
$shell = new quote_cleaner();
$shell->run();


