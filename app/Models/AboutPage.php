<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'mission_title',
        'mission_content',
        'how_it_works_content',
        'benefits_content',
    ];
}