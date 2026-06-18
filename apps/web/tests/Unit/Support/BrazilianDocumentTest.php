<?php

namespace Tests\Unit\Support;

use App\Support\BrazilianDocument;
use PHPUnit\Framework\TestCase;

class BrazilianDocumentTest extends TestCase
{
    public function test_it_validates_numeric_and_alphanumeric_cnpj(): void
    {
        $this->assertTrue(BrazilianDocument::isValid('12.345.678/0001-95'));
        $this->assertTrue(BrazilianDocument::isValid('12.ABC.345/01DE-35'));
        $this->assertSame('12ABC34501DE35', BrazilianDocument::normalize('12.abc.345/01de-35'));
    }

    public function test_it_validates_cpf_and_rejects_invalid_documents(): void
    {
        $this->assertTrue(BrazilianDocument::isValid('529.982.247-25'));
        $this->assertFalse(BrazilianDocument::isValid('12.ABC.345/01DE-34'));
        $this->assertFalse(BrazilianDocument::isValid('00.000.000/0000-00'));
        $this->assertFalse(BrazilianDocument::isValid('111.111.111-11'));
        $this->assertFalse(BrazilianDocument::isValid('ABC'));
    }
}
