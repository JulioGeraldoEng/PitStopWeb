<?php

namespace App\Helpers;

class FormatarTelefone
{
    public static function formatar($telefone)
    {
        if (!$telefone) return '';
        
        $telefone = preg_replace('/\D/', '', $telefone);
        
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
        }
        
        if (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
        }
        
        return $telefone;
    }
}