<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;
    
    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'content',
        'status',
        'scheduled_at',
        'published_at',
        'platform',
        'ai_generated',
        'engagement_data',
    ];
    
    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'engagement_data' => 'array',
        'ai_generated' => 'boolean',
    ];
    
    /**
     * Obtém o usuário que possui esta postagem.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Obtém a categoria desta postagem.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * As tags que pertencem a esta postagem.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }
    
    /**
     * Escopo para postagens em rascunho.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'rascunho');
    }
    
    /**
     * Escopo para postagens prontas para publicação.
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'pronto');
    }
    
    /**
     * Escopo para postagens publicadas.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'publicado');
    }
    
    /**
     * Escopo para postagens agendadas.
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '>', now())
                    ->where('status', 'pronto');
    }
}
