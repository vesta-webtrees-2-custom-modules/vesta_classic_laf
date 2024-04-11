<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Elements;

use Fisharebest\Webtrees\Elements\SubmitterText;

class Level1SubmitterText extends SubmitterText {

    /**
     * An edit control for this data.
     *
     * @param string $id
     * @param string $name
     * @param string $value
     *
     * @return string
     */
    public function editTextArea(string $id, string $name, string $value): string
    {
        return '<textarea class="form-control" id="' . e($id) . '" name="' . e($name) . '" rows="12" dir="auto">' . e($value) . '</textarea>';
    }
}
