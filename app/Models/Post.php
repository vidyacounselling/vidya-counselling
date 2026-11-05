<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected function casts(): array { return ['published_at'=>'immutable_datetime','content_updated_at'=>'immutable_datetime','reviewed_at'=>'immutable_datetime','comments_enabled'=>'boolean','noindex'=>'boolean','nofollow'=>'boolean','version'=>'integer']; }
    public function author() { return $this->belongsTo(User::class, 'author_id')->withTrashed(); }
    public function category() { return $this->belongsTo(Category::class); }
    public function tags() { return $this->belongsToMany(Tag::class); }
    public function media() { return $this->belongsTo(Media::class); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function revisions() { return $this->hasMany(PostRevision::class); }
    public function scripts() { return $this->hasMany(BlogScript::class); }
    public function scopeEligible($query) { return $query->whereIn('status',['published','scheduled'])->whereNotNull('published_at')->where('published_at','<=',now()); }
    public function isPublic(): bool { return ! $this->trashed() && in_array($this->status,['published','scheduled'],true) && $this->published_at && $this->published_at->lte(now()); }
    public function getEffectiveStatusAttribute(): string { return $this->trashed() ? 'trash' : ($this->isPublic() ? 'published' : $this->status); }
    public function getPublicUrlAttribute(): string { return url('/blog/'.$this->slug); }
    public function getCanonicalAttribute(): string { return $this->canonical_url ?: $this->public_url; }
    public function getDescriptionAttribute(): string { return $this->seo_description ?: ($this->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($this->body_html ?? ''),155)); }
}
