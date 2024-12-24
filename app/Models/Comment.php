<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'content_id', 'content_type', 'parent_comment_id', 'comment_text', 'gif'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Polymorphic relation for content (e.g., videos, recipes)
    public function content(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    // Relationship with reactions
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }
    public function reactionsSummary(): HasMany
    {
        return $this->hasMany(Reaction::class)
            ->select('emoji', DB::raw('count(*) as count'))
            ->groupBy('emoji');
    }

    protected $casts = [
        'gif' => 'array',
    ];

    /**
     * Define the relationship to fetch the user who created the comment.
     */
}
