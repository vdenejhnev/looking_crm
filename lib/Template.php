<?php

namespace App;

class Template {
    private $basePath;
    private $globalData;

    public function __construct($basePath, $globalData = []) {
        $this->basePath = $basePath;
        $this->globalData = $globalData;
    }

    public function render($path, $data = []) {
        $globalData = $this->globalData;
        $this->globalData = array_merge($this->globalData, $data);

        extract($this->globalData);

        ob_start();
        include "$this->basePath$path.php";
        $result = ob_get_contents();
        ob_end_clean();

        $this->globalData = $globalData;

        return $result;
    }
}
