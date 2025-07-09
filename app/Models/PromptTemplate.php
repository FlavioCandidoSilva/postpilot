<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'content',
        'category',
        'is_default',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Obtém o usuário que possui este template.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o conteúdo XML do template é válido.
     *
     * @return bool
     */
    public function hasValidXml()
    {
        // Verificar se o conteúdo é um XML válido
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($this->content);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        return $xml !== false && empty($errors);
    }

    /**
     * Extrai as variáveis do template XML.
     *
     * @return array
     */
    public function extractVariables()
    {
        $variables = [];
        
        // Verificar se o XML é válido antes de tentar extrair variáveis
        if (!$this->hasValidXml()) {
            return $variables;
        }
        
        // Carregar o XML
        $xml = simplexml_load_string($this->content);
        
        // Extrair nomes de tags como variáveis potenciais
        $this->extractTagsAsVariables($xml, $variables);
        
        return array_unique($variables);
    }
    
    /**
     * Extrai recursivamente os nomes de tags como variáveis potenciais.
     *
     * @param \SimpleXMLElement $element
     * @param array &$variables
     * @return void
     */
    private function extractTagsAsVariables($element, &$variables)
    {
        foreach ($element->children() as $child) {
            $name = $child->getName();
            if (!in_array($name, ['task', 'instrucoes', 'restricoes', 'exemplos', 'objetivo'])) {
                $variables[] = $name;
            }
            
            // Recursivamente extrair variáveis de elementos filhos
            $this->extractTagsAsVariables($child, $variables);
        }
    }
}
