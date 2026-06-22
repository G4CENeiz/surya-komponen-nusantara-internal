<?php

namespace App\Filament\Hrd\Forms\Components;

use Filament\Forms\Components\Field;

class LeafletMap extends Field
{
    protected string|\Closure $latStatePath = 'latitude';

    protected string|\Closure $lngStatePath = 'longitude';

    protected string|\Closure $radiusStatePath = 'radius_meters';

    protected Closure|float $defaultLat = -6.22689;

    protected Closure|float $defaultLng = 106.81473;

    protected int|\Closure $defaultZoom = 14;

    public function setUp(): void
    {
        parent::setUp();

        $this->view('filament.hrd.forms.components.leaflet-map');
    }

    public function latStatePath(string|\Closure $statePath): static
    {
        $this->latStatePath = $statePath;

        return $this;
    }

    public function getLatStatePath(): string
    {
        return 'data.'.$this->evaluate($this->latStatePath);
    }

    public function lngStatePath(string|\Closure $statePath): static
    {
        $this->lngStatePath = $statePath;

        return $this;
    }

    public function getLngStatePath(): string
    {
        return 'data.'.$this->evaluate($this->lngStatePath);
    }

    public function radiusStatePath(string|\Closure $statePath): static
    {
        $this->radiusStatePath = $statePath;

        return $this;
    }

    public function getRadiusStatePath(): string
    {
        return 'data.'.$this->evaluate($this->radiusStatePath);
    }

    public function defaultLatLng(float|\Closure $lat, float|\Closure $lng): static
    {
        $this->defaultLat = $lat;
        $this->defaultLng = $lng;

        return $this;
    }

    public function getDefaultLat(): float
    {
        return $this->evaluate($this->defaultLat);
    }

    public function getDefaultLng(): float
    {
        return $this->evaluate($this->defaultLng);
    }

    public function defaultZoom(int|\Closure $zoom): static
    {
        $this->defaultZoom = $zoom;

        return $this;
    }

    public function getDefaultZoom(): int
    {
        return $this->evaluate($this->defaultZoom);
    }
}
