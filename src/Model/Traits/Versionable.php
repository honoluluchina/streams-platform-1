<?php namespace Anomaly\Streams\Platform\Model\Traits;

use Anomaly\Streams\Platform\Version\Contract\VersionInterface;
use Anomaly\Streams\Platform\Version\VersionModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Versionable
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
trait Versionable
{

    /**
     * Versionable flag.
     *
     * @var bool
     */
    protected $versionable = false;

    /**
     * The versioned attributes.
     *
     * @var array
     */
    protected $versionedAttributes = [];

    /**
     * The versioned attribute changes.
     *
     * @var array
     */
    protected $versionedAttributeChanges = [];

    /**
     * Return if the model should version or not.
     *
     * @return bool
     */
    public function shouldVersion()
    {
        if ($this->wasRecentlyCreated && count($this->getVersionedAttributeChanges())) {
            return true;
        }

        $nonVersionedAttributes = isset($this->nonVersionedAttributes)
            ? $this->nonVersionedAttributes
            : [];

        $ignoredAttributes = array_merge(
            $nonVersionedAttributes,
            [
                'created_at',
                'created_by_id',
                'updated_at',
                'updated_by_id',
                'deleted_at',
                'deleted_by_id',
            ]
        );

        return (count(array_diff_key($this->versionedAttributeChanges, array_flip($ignoredAttributes))) > 0);
    }

    /**
     * Get the versionable flag.
     *
     * @return bool
     */
    public function isVersionable()
    {
        return $this->versionable;
    }

    /**
     * Set the versionable flag.
     *
     * @param $versionable
     * @return $this
     */
    public function setVersionable($versionable)
    {
        $this->versionable = $versionable;

        return $this;
    }

    /**
     * Enable versioning.
     *
     * @return $this
     */
    public function enableVersioning()
    {
        $this->versionable = true;

        return $this;
    }

    /**
     * Disable versioning.
     *
     * @return $this
     */
    public function disableVersioning()
    {
        $this->versionable = false;

        return $this;
    }

    /**
     * Return versioned attributes.
     *
     * @return array
     */
    public function getVersionedAttributes()
    {
        return $this->versionedAttributes;
    }

    /**
     * Return if the attribute is
     * translatable or not.
     *
     * @param $key
     * @return bool
     */
    public function isVersionedAttribute($key)
    {
        return in_array($key, $this->versionedAttributes);
    }

    /**
     * Set the versioned attribute changes (dirty).
     *
     * @param array $changes
     * @return $this
     */
    public function setVersionedAttributeChanges(array $changes)
    {
        $this->versionedAttributeChanges = $changes;

        return $this;
    }

    /**
     * Get the versioned attribute changes (dirty).
     *
     * @return array
     */
    public function getVersionedAttributeChanges()
    {
        return $this->versionedAttributeChanges;
    }

    /**
     * Return the versions relation.
     *
     * @return HasMany
     */
    public function versions()
    {
        $this->morphMany(VersionModel::class, 'versionable');
    }

    /**
     * Return the latest version.
     *
     * @return VersionInterface|null
     */
    public function currentVersion()
    {
        return $this
            ->versions()
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Return the previous version.
     *
     * @return VersionInterface
     */
    public function previousVersion()
    {
        return $this
            ->versions()
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->offset(1)
            ->first();
    }

}
