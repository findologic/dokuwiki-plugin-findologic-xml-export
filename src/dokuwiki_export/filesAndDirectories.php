<?php

/**
 *  This is the Dokuwiki export made by D. Brader for FINDOLOGIC.
 *  If any bugs occur, please contact the support team (support@findologic.com).
 */

require __DIR__ . '/../../vendor/autoload.php';

class filesAndDirectories {

    /**
     * This function gets all dokuwiki pages without directories. Just .txt files.
     *
     * @param string $dir Directory of the dokuwiki pages.
     * @param array $results Ignore this parameter. It is just used for the function itself.
     * @return array $results All files that end with .txt (for the dokuwiki).
     */
    public function getFilesAndDirectories($dir, & $results = array())
    {
        $files = scandir($dir);
        $fileEnd = ".txt";

        foreach ($files as $key => $value) {
            $path = realpath($dir . "/" . $value);

            if (!is_dir($path)) {

                if ($this->fileEndsWith($path, $fileEnd)) {

                    $results[] = $path;

                }

            } // Ignore the . and .. directories.
            else if (!in_array($value, array('.', '..'))) {
                $this->getFilesAndDirectories($path, $results);
            }
        }
        return $results;
    }

    /*$fileEnd = '.txt';
    $loop = true;
    $directory = array();
    $directory[0] = $dir;
    $loops = 0;

    while ($loop == true){

        $filesAndDirectories = scandir($directory[$loops]);


        foreach ($filesAndDirectories as $key => $value) {
            $path = realpath($dir . '/' . $value);
            if ($path == false) {
                $loop = false;
                return $results;
            }

            if (!is_dir($path)) {
                //echo "txt:  ";
                //var_dump($path);

                if ($this->fileEndsWith($path, $fileEnd)) {
                    $files[] = $path;
                }
            }
            else {
                //echo "dir:  ";
                //var_dump($path);
                $directory[$loops + 1] = $path;
            }
        }
        if (!array_key_exists(($loops + 1), $directory)) {
            $loop = false;
        }
        if (!$directory[$loops + 1]) {
            $loop = false;
        }
        if (empty($directory[$loops + 1])){
            $loop = false;
        }
        print_r($files);
        $loops++;
    }

    return $results;
}*/

    /**
     * Check if the first string ends with the second one.
     *
     * @param string $string1 Just some kind of string.
     * @param string $string2 Another string.
     * @return boolean Returns true if the first string ends with the second string.
     */
    public function fileEndsWith($string1, $string2)
    {
        return substr($string1, -strlen($string2)) == $string2 ? true : false;
    }
}