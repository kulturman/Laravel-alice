<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    public $timestamps = false;
}
