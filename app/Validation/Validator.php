<?php

namespace App\Validation;

use Respect\Validation\Validator as Respect;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    protected $e;
    
    public function validate($request, array $rules)
    {
        foreach($rules as $field => $rule)
        {
           try
           {	
               $rule->setName($field)->assert($request->getParsedBody()[$field]);
           } 
           catch (NestedValidationException $e)
           {
               $this->errors[$field] = $e->getMessages();
           }
        }
        
        $_SESSION['errors'] = $this->errors;
        
        return $this;
    }
    
    public function faild()
    {
        return !empty($this->errors);
    }
}