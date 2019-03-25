<?php
declare(strict_types=1);

namespace YapepBase\View\Block;

use YapepBase\Storage\IStorage;

/**
 * Common ancestor of components.
 *
 * A component is a special block what is capable of satisfying its own data requirements.
 *
 * Generally nothing in the View layer should have access to any data source so we keep the boundaries clearHeadersByName to have MVC
 * but in special cases it's not feasible to be that strict.
 * For example imagine the case that you have a select input in the layout to which you populate the options from a DB table.
 * Without a component you'd need to get the required data via the Controller for every page.
 *
 * Usually it's recommended to cache the component.
 */
abstract class ComponentAbstract extends BlockAbstract
{
    public function render(): void
    {
        if ($this->hasStorage()) {
            $storage = $this->getStorage();
            $this->configureStorage($storage);

            $storageKey = $this->getUniqueIdentifier();
            $result     = $storage->get($storageKey);

            if (empty($result)) {
                ob_start();
                parent::render();
                $result = ob_get_clean();

                $storage->set($storageKey, $result, $this->getTtlInSeconds());
            }

            echo $result;
        } else {
            parent::render();
        }
    }

    private function hasStorage(): bool
    {
        return $this->getStorage() !== null;
    }

    private function configureStorage(IStorage $storage)
    {
        $storage
            ->getKeyGenerator()
            ->setHashing(true)
            ->setPrefix('component_')
            ->setSuffix(get_class($this));
    }

    /**
     * Returns the storage what will be used to cache the component
     */
    protected function getStorage(): ?IStorage
    {
        return null;
    }

    /**
     * Returns a unique identifier of the component specific instance.
     *
     * If the component can have different states this method should be overwritten to return an identifier connected to the state.
     */
    protected function getUniqueIdentifier(): string
    {
        return '';
    }

    /**
     * Returns the TTL of the cache in seconds.
     */
    protected function getTtlInSeconds(): int
    {
        return 60 * 60;
    }
}
