<?php


namespace spoova\core\commands\Make;

use spoova\core\classes\FileManager;
use spoova\core\commands\Cli;
use spoova\core\commands\Make\MkBase;

class MkMigrator extends MkBase{


    public function build() : bool{

        $args = static::$args;

        $arg1 = $args[0] ?? '';
        $arg2 = $args[1] ?? '';

        $filename = $arg1;      

        $class = $arg1;
        $class = ltrim(to_frontslash($class, true), '/');
        $class = to_frontslash($class, true);
        $classDir  = dirname($class);
        $classDir  = ($classDir == '.')? '' : $classDir;
        $className = ucfirst(basename($class));

        Cli::textView(Cli::danger(Cli::emo('point-list').' add:migrator ').Cli::warn($filename));
        Cli::break(2);

        if(strpos($filename, '.') !== false){
            Cli::textView(Cli::error('invalid character supplied on file name'), 2, "|2");
            return false; 
        }

        if(count($args) > 1){
            Cli::textView(Cli::error('invalid number of arguments count!'), 2, "|2");
            return false;             
        }

        $filename = rtrim($filename,'.php').".php";      

        $filepath = docroot."/core/migrations/{$filename}";        

        $prefix = 'M'.time().'_';

        $filename = $prefix.basename($filename);
        $filepath = dirname($filepath).'/'.$prefix.basename($filepath);

        /* Note:: all space variables have no trail slash */

        /* class subnamespace in window\Models if subnamespace exists */
        $classSpace = to_namespace($classDir); 
        
        /* class namespace starting from windows folder  */
        $migrateSpace  = to_namespace('core\\migrations\\'.$classSpace);
        
        /* class full folder namespace */
        $nameSpace = scheme($migrateSpace, false);    

        /* class full namespace */
        $fileNameSpace = $nameSpace.'\\'.$className;

        /* class relative migrations directory */
        $fileDir  = to_frontslash($migrateSpace);
        
        /* class absolute file path */
        $fileLoc  = $fileDir.'\\'.$className.'.php'; /* relative file path */


        $Filemanager = new FileManager;
       
        if($Filemanager->openFile(true, $filepath)) {



            $content = <<<SCRIPT

              public function up() {

                DBSCHEMA::CREATE(\$this, function(DRAFT \$DRAFT){

                    //\$DRAFT::Method();

                });

              }

              public function down() {

                

              }  

              public function table() : string {

                return 'tablename';

              }

            SCRIPT; 
            
            $format = self::classFormat([
                'namespace' => $nameSpace, 
                'class'     => $prefix.$className, 
                'methods'   => $content,
                'use'       => [
                    'spoova\core\classes\DBSchema\DBSCHEMA',
                    'spoova\core\classes\DBSchema\DRAFT'
                ]
            ]);
            file_put_contents($filepath, $format);
            
            if(is_file($filepath) && class_exists($nameSpace.'\\'.$prefix.$className)){

                Cli::textView("migration file ".Cli::alert($prefix.$className)." created successfully in ".Cli::warn($fileDir));
                Cli::break(2);
                return true;

            }  else {

                
                Cli::textView("migration file ".Cli::alert($className)." failed to create in ".Cli::danger($fileDir)." directory.");
                Cli::break(2);

            }           

        }

        return false;  
    }


}