<?php

Schema::create('veiculos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('marca');
    $table->string('modelo');
    $table->string('placa');
    $table->year('ano');
    $table->timestamps();
});