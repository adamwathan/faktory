# To do

- Clean up the relationship stuff. Make each relationship method store a `Relationship` object in the attributes array, and execute that relationship using a different strategy depending on whether or not build or create is called.

I'd like this...

```php
$f->songs = $f->hasMany('song', 'album_id', 5);
```

...to persist related objects on `create` and NOT store them on the `songs` attribute, and to *just* store them on the `songs` attribute *without* persisting on `build`.

Something like this maybe, but who the fuck really knows...

```php
$this->strategy->getAttributeValues($attributes);

class Create extends BuildStrategy
{
    public function getAttributeValues($attributes)
    {
        $processed_attributes = [];
        foreach ($attributes as $attribute => $value) {
            if ($value instanceof Relationship) {
                $this->createRelationship($value);
                continue;
            }
            $processed_attributes[$attribute] = $this->getAttributeValue($value);
        }
    }
}

class Build extends BuildStrategy
{
    public function getAttributeValues($attributes)
    {
        $processed_attributes = [];
        foreach ($attributes as $attribute => $value) {
            $processed_attributes[$attribute] = $this->getAttributeValue($value);
        }
    }

    public function getAttributeValue($value)
    {
        if ($value instanceof Relationship) {
            return $this->buildRelationship($value);
        }
        if (is_callable($value)) {
            return $value($this, $this->sequence);
        }
        return $value;
    }
}
```
