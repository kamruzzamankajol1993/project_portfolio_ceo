<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SeoPage extends Model
{
    protected $fillable = ['path','title','description','keywords','robots','canonical','og_title','og_description','og_image','enabled'];
    protected function casts(): array { return ['enabled'=>'boolean']; }
    public function imageUrl(): ?string { return $this->og_image ? asset('public/'.$this->og_image) : null; }
}
