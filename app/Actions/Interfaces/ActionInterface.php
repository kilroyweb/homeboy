<?php

namespace App\Actions\Interfaces;

interface ActionInterface{

    public function confirmationMessage();

    public function actionMessage();

    public function run();

}