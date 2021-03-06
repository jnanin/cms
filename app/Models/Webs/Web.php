<?php

namespace App\Models\Webs;

use App\Models\Activity;
use App\Models\BaseModel;
use App\Models\Files\File;
use App\Models\Forms\Form;
use App\Models\Pages\Page;
use App\Models\Posts\Post;
use App\Models\Users\User;
use App\Models\Location\City;
use App\Models\Animals\Animal;
use App\Models\Location\State;
use App\Models\Posts\Category;
use App\Models\Widgets\Widget;
use App\Models\Finances\Finance;
use App\Models\Location\Country;
use App\Models\Partners\Partner;
use App\Models\Calendar\Calendar;
use App\Helpers\Traits\LogsActivity;
use App\Models\Veterinarians\Veterinary;
use Illuminate\Database\Eloquent\SoftDeletes;

class Web extends BaseModel
{
    use SoftDeletes, LogsActivity;

    protected $table = 'webs';
    protected $casts = ['config' => 'array'];
    protected $with = ['config'];
    protected $fillable = [
        'name', 'description', 'email', 'phone', 'address', 'city_id', 'state_id', 'country_id',
        'contact_name', 'contact_email', 'contact_phone', 'logo', 'config',
    ];

    public function getLangAttribute()
    {
        return 'es';
    }

    public function getTheme()
    {
        return $this->config['theme'];
    }

    public function getUrl(bool $subdomain = false) : string
    {
        if ($this->domain && ! $subdomain) {
            return 'http://'.$this->domain;
        }

        return 'http://'.$this->subdomain.'.protecms.com';
    }

    public function getDomainOrSubdomain()
    {
        if ($this->domain) {
            return $this->domain;
        }

        return $this->subdomain.'.protecms.com';
    }

    public function getConfig($key, $value = null)
    {
        foreach ($this->config as $i => $config) {
            if ($config->key == $key) {
                return $this->config[$i]->value;
            }
        }

        return $value ?: null;
    }

    public function hasConfig($key)
    {
        return $this->getConfig($key) ? true : false;
    }

    public function setConfig($key, $value)
    {
        if (! $this->config()->where('key', $key)->exists()) {
            $this->config()->create([
                'key'   => $key,
                'value' => $value,
            ]);
        } else {
            $this->config()->where('key', $key)->update([
                'value' => $value,
            ]);
        }

        return $this;
    }

    public function setConfigs(array $config)
    {
        foreach ($config as $key => $value) {
            if (! $this->config()->where('key', $key)->exists()) {
                $this->config()->create([
                    'key'   => $key,
                    'value' => $value,
                ]);
            }
        }

        return $this;
    }

    public function unsetConfig($key)
    {
        if ($config = $this->config()->where('key', $key)->first()) {
            $config->delete();
        }

        return $this;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, ['country_id', 'state_id', 'city_id'])) {
            if ($value === '') {
                $value = null;
            }
        }

        parent::setAttribute($key, $value);
    }

    public function getStorageFolder($path = null, $local = true)
    {
        if ($local) {
            $storage_path = storage_path('app/web/'.$this->id);
        } else {
            $storage_path = 'web/'.$this->id;
        }

        if ($path) {
            $storage_path = $storage_path.'/'.$path;
        }

        return $storage_path;
    }

    public function volunteers()
    {
        return $this->hasMany(User::class)->whereIn('type', ['volunteer', 'admin']);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }

    public function posts_categories()
    {
        return $this->hasMany(Category::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function calendar()
    {
        return $this->hasMany(Calendar::class);
    }

    public function widgets()
    {
        return $this->hasMany(Widget::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    public function veterinarians()
    {
        return $this->hasMany(Veterinary::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function finances()
    {
        return $this->hasMany(Finance::class);
    }

    public function config()
    {
        return $this->hasMany(Config::class);
    }

    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
