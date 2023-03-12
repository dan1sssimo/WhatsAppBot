<?php

class ultramsgDictionary
{
    public function welcomeIntent()
    {
        $hiArray = ["hi", "hello", "hola", "مرحبا", "Selam", "Привет", "Oi", "नमस्ते"];

        return $hiArray;
    }

    public function welcomeResponses()
    {
        $welcomeArray = ["Hi", "welcome , how i can help you", "I'm pleased to talk to you"];

        return $welcomeArray[rand(0, count($welcomeArray) - 1)];
    }

}
