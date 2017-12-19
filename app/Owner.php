<?php

namespace App;

use LaravelEnso\CommentsManager\app\Traits\Commentable;
use LaravelEnso\Contacts\app\Traits\Contactable;
use LaravelEnso\Core\app\Models\Owner as Owners;
use LaravelEnso\DocumentsManager\app\Traits\Documentable;
use LaravelEnso\RoAddresses\app\Traits\Addressable;

class Owner extends Owners
{
    use Addressable, Contactable, Commentable, Documentable;

    protected $fillable = ['name', 'description', 'is_active'];

    public function users()
    {
        return $this->hasMany('App\User');
    }
}
