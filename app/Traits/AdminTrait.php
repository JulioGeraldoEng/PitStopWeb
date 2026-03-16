<?php
// app/Traits/AdminTrait.php

namespace App\Traits;

trait AdminTrait
{
    /**
     * Verifica se o usuário atual é admin
     */
    protected function isAdmin()
    {
        return auth()->check() && auth()->user()->tipo === 'admin';
    }

    /**
     * Verifica e aborta se não for admin
     */
    protected function authorizeAdmin()
    {
        if (!$this->isAdmin()) {
            abort(403, 'Acesso negado! Apenas administradores podem acessar esta área.');
        }
    }
}