<?php
  class hFrameworkEventLibrary extends hPlugin { private $events; public function hConstructor() { } public function ricochet($event, $arguments) {      return $this->fire( $event, $arguments, true ); } public function fire($event, $arguments, $ricochet = false) {  if (isset($this->events[$event])) { $attachedEvents = $this->events[$event]; if (is_array($attachedEvents)) { foreach ($attachedEvents as $eventCounter => $attachedEvent) { if (is_callable($attachedEvent['plugin'])) { $returnValue = call_user_func_array( $attachedEvent['plugin'], $arguments ); if ($ricochet) { return $returnValue; } } else if (is_object($attachedEvent['plugin'])) { $returnValue = call_user_method_array( $attachedEvent['method'], $attachedEvent['plugin'], $arguments ); if ($ricochet) { return $returnValue; } } else if (is_string($attachedEvent['plugin'])) { if (empty($attachedEvent['method'])) { $returnValue = $this->library( $attachedEvent['plugin'], $arguments ); if ($ricochet) { return $returnValue; } } else { $this->events[$event][$eventCounter]['plugin'] = $this->library( $this->events[$event][$eventCounter]['plugin'] ); $returnValue = call_user_method_array( $attachedEvent['method'], $this->events[$event][$eventCounter]['plugin'], $arguments ); if ($ricochet) { return $returnValue; } } } } } } } public function on($event, $plugin, $method = nil) { $name = nil; if (strstr($event, '.')) { list($event, $name) = explode('.', $event); }  if (!isset($this->events[$event])) { $this->events[$event] = array(); } array_push( $this->events[$event], array( 'plugin' => $plugin, 'method' => $method, 'name' => $name ) ); } public function off($event, $plugin = nil, $method = nil) { $name = nil; if (strstr($event, '.')) { list($event, $name) = explode('.', $event); } if (empty($name) && empty($plugin)) { unset($this->events[$event]); } else if (isset($this->events[$event]) && is_array($this->events[$event])) { foreach ($this->events[$event] as $eventCounter => $attachedEvent) { if (!empty($name) && $name == $attachedEvent['name']) { unset($this->events[$event][$eventCounter]); } else if (!empty($plugin) && $plugin == $attachedEvent['plugin']) { if (!empty($method)) { if ($method == $attachedEvent['method']) { unset($this->events[$event][$eventCounter]); } } else { unset($this->events[$event][$eventCounter]); } } } } } } ?>