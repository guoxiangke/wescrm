<?php

namespace App\Http\Livewire;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ToogleButton extends Component
{
	public Model $model;
    public string $field;
    public bool $isActive;
    public function mount()
    {
        $this->isActive = (bool) $this->model->getAttribute($this->field);
    }

    public function render()
    {
        return view('components.toogle-button');
    }

    public function updaing($field, $value)
    {   
        $this->model->setAttribute($this->field,$value)->save();
    }
}