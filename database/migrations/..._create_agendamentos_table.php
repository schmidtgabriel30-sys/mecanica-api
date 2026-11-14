<?php

Schema::create('agendamentos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('veiculo_id')->constrained('veiculos')->onDelete('cascade');
    $table->foreignId('servico_id')->constrained('servicos')->onDelete('cascade');
    $table->dateTime('data_agendada');
    $table->string('status')->default('pendente'); // pendente, aprovado, concluido
    $table->text('observacoes')->nullable();
    $table->timestamps();
});