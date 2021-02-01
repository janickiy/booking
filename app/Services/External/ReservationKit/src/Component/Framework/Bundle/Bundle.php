<?php

namespace ReservationKit\src\Component\Framework\Bundle;

class Bundle
{
    protected $name;
    
    protected $path;
    
    /**
     * Возвращает namespace бандла.
     *
     * @return string namespace бандла
     */
    public function getBundleNamespace()
    {
        $class = get_class($this);

        return substr($class, 0, strrpos($class, '\\'));
    }

    /**
     * Возвращает путь к директории бандла.
     *
     * @return string Абсолютный путь директории бандла
     */
    public function getPath()
    {
        if (null === $this->path) {
            $reflected = new \ReflectionObject($this);
            $this->path = dirname($reflected->getFileName());
        }

        return $this->path;
    }
    
    /**
     * Возвращает название бандла (короткое название).
     *
     * @return string The Bundle name
     */
    final public function getBundleName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = get_class($this);
        $pos = strrpos($name, '\\');

        return $this->name = false === $pos ? $name : substr($name, $pos + 1);
    }
}