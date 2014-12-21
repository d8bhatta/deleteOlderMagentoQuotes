Delete Magento Older Quotes
========================

How to run the quotes cleaning shell script?
Put the file inside shell directory. quote_cleaner.php file includes abstract.php file which provides required DB connection and access to core files.

$ php quote_cleaner.php --help

      Usage:  php -f quote_cleaner.php -- [options]
      
      --limit <delete_limit>        Delete quote limit (max 100.000)
      
      --regQuotes <reg_quotes>      How older registered quotes you want to delete, normally 60 days.
      
      --anonQuotes <anon_quotes>    How older anonymous  quotes you want to delete, normally 30 days.
      
      help                          This help
      
      

The script can be directly run without any arguments  like


$ php quote_cleaner.php


If we run without any arguments, it takes default values from code. Default values are


limit = 50000 ( delete 50000 records at a time)


regQuotes = 60 ( in days)


anonQuotes = 30 ( in days)



It can more controlled using arguments. So if you want to set your desired number of argyments, it goes like below:


 $ php quote_cleaner.php --limit 50000 --regQuotes 30 --anonQuotes 20


Total Execution time depends on number of deleting quotes, nomrally it takes 5-7  minutes for 1 million records.

 
NOTE: PLEASE BACKUP YOUR DATABASE BEFORE YOU RUN THIS SHELL SCRIPT. Command to back your entire DB goes here:
mysqldump -u USERNAME -pYOURPASSWORD  dbname>~/Desktop/magento-backup.sql

Best of luck!

Thank you
