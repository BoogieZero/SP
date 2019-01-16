<?php 
    /*
    Class provides means to log messages.
    */
    class Log{
        //log file
        const path_msg = "log/msg.txt";
        
        /*
        Writes message to log file.
        @msg    message
        @file   filename from which was the message sent
        */
        public static function msg($msg, $file = "---") {
            if(!file_exists(Log::path_msg)){
                Log::clear_msg();
            }
            error_log(print_r(Log::getStamp()."[".basename($file)."]\t".$msg.PHP_EOL."", TRUE), 3, Log::path_msg);
        }
        
        /*
        Clears log file.
        */
        public static function clear_msg(){
            $fh = fopen(Log::path_msg, 'w') or die("Can't create file");
                fclose($fh);
        }
        
        /*
        Creates time stamp for log record.
        @return timestamp
        */
        private static function getStamp(){
            return '['.date("d/m/Y @ g:i:s", time()).']'."\t";
        }
        
    }
?>
