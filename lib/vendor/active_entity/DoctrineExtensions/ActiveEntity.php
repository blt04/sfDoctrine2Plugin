<?php

/*
 *  $Id: ActiveEntity.php 4947 2008-09-12 13:16:05Z jwage $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineExtensions;

/**
 * Abstract class to extend your entities from to give a layer which gives you 
 * the functionality magically offered by Doctrine_Record in Doctrine 1. This
 * class is not usually recommended to use as it is adds another layer of overhead
 * and magic. It is meant as a layer for backwards compatability with Doctrine 1.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
abstract class ActiveEntity implements \ArrayAccess
{
    const STATE_LOCKED = 1;

    private $_state;
    private $_metadata;
    static $_lockedObjects = array();

    protected static $_em;

    public static function setEntityManager(\Doctrine\ORM\EntityManager $em)
    {
        self::$_em = $em;
    }

    public function save($em = null)
    {
        $em = $em ? $em : self::$_em;
        $em->persist($this);
    }

    public function delete($em = null)
    {
        $em = $em ? $em : self::$_em;
        $em->remove($this);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->$key);
    }

    public function offsetExists($key)
    {
        return isset($this->$key);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        unset($this->$key);
    }

    public function get($key)
    {
        $methodName = 'get' . ucfirst($key);
        
        return (is_callable(array($this, $methodName))) 
            ? $this->$methodName() : $this->$key;
    }

    public function set($key, $value)
    {
        $methodName = 'set' . ucfirst($key);
        
        if (is_callable(array($this, $methodName))) {
            $this->$methodName($value);
        } else {
            $this->$key = $value;
        }
    }

    public function fromArray(array $array, $obj = null)
    {
      if ($obj === null) {
          $obj = $this;
      }

      foreach ($array as $key => $value) {
          if (is_array($value)) {
              $this->fromArray($value, $obj->$key);
          } else {
              $obj->set($key, $value);
          }
      }
    }

    public function toArray($obj = null)
    {
        if ($obj === null) {
            $obj = $this;
        }

        $array = array();
        
        if ($obj instanceof \DoctrineExtensions\ActiveEntity) {
            if ($obj->_state === self::STATE_LOCKED) {
                return array();
            }

            $originalState = $obj->_state;

            $reflFields = self::$_em->getClassMetadata(get_class($this))->reflFields;
            
            foreach ($reflFields as $name => $reflField) {
                $value = $this->$name;
                
                if ($value instanceof \DoctrineExtensions\ActiveEntity) {
                    $obj->_state = self::STATE_LOCKED;
                    
                    if ($result = $value->toArray()) {
                        $array[$name] = $result;
                    }
                } else if ($value instanceof \Doctrine\Common\Collections\Collection) {
                    $obj->_state = self::STATE_LOCKED;
                    
                    $array[$name] = $this->toArray($value);
                } else {
                    $array[$name] = $value;
                }
            }

            $obj->_state = $originalState;
        } else if ($obj instanceof \Doctrine\Common\Collections\Collection) {
            foreach ($obj as $key => $value) {
                if (in_array(spl_object_hash($obj), self::$_lockedObjects)) {
                    $array[$key] = $obj;
                    continue;
                }
                self::$_lockedObjects[] = spl_object_hash($obj);
                if ($result = $this->toArray($value)) {
                    $array[$key] = $result;
                }
            }
        }

        self::$_lockedObjects[] = array();
        return $array;
    }

    public function __toString()
    {
        return var_export($this->obtainIdentifier(), true);
    }

    public function obtainMetadata()
    {
      if ( ! $this->_metadata) {
          $this->_metadata = self::$_em->getMetadataFactory()->getMetadataFor(get_class($this));
      }
      
      return $this->_metadata;
    }

    public function obtainIdentifier()
    {
        return $this->obtainMetadata()->getIdentifierValues($this);
    }

    public function exists()
    {
        $id = self::$_em->getMetadataFactory()->getMetadataFor(get_class($this))->getIdentifierValues($this);
        
        return (self::$_em->contains($this) && ! empty($id)) ? true : false;
    }

    public function __call($method, $arguments)
    {
        $func = substr($method, 0, 3);
        $fieldName = substr($method, 3, strlen($method));
        $fieldName = lcfirst($fieldName);

        if ($func == 'get') {
            return $this->$fieldName;
        } else {
            $this->$fieldName = $arguments[0];
        }
    }

    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array(array(self::$_em->getRepository(get_called_class()), $method), $arguments);
    }
}