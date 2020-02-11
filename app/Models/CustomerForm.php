<?php

namespace App\Models;

use Encore\Admin\Form;

class CustomerForm extends Form  //自定义的form表单  为了验证
{
    public function validationMessages1($input)
    {
        return $this->validationMessages($input);
    }

}
