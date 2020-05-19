<?php

$httpRegex = "~^((https?)://).+$~";


if(preg_match($httpRegex, 'http://')) {
    echo "toto \n";
}

echo "end \n";


