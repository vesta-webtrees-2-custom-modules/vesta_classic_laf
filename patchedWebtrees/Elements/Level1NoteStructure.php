<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Elements;

use Fisharebest\Webtrees\Elements\NoteStructure;
use Fisharebest\Webtrees\Elements\XrefNote;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use function e;
use function view;

class Level1NoteStructure extends NoteStructure {
    
    /**
     * An edit control for this data.
     *
     * @param string $id
     * @param string $name
     * @param string $value
     * @param Tree   $tree
     *
     * @return string
     */
    //same as parent::edit, except for Level1SubmitterText instead of SubmitterText
    public function edit(string $id, string $name, string $value, Tree $tree): string
    {
        $submitter_text = new Level1SubmitterText('');
        $xref_note      = new XrefNote('');

        // Existing shared note.
        if (preg_match('/^@' . Gedcom::REGEX_XREF . '@$/', $value)) {
            return $xref_note->edit($id, $name, $value, $tree);
        }

        // Existing inline note.
        if ($value !== '') {
            return $submitter_text->edit($id, $name, $value, $tree);
        }

        $options = [
            'inline' => I18N::translate('inline note'),
            'shared' => I18N::translate('shared note'),
        ];

        // New note - either inline or shared
        return
            '<div id="' . e($id) . '-note-structure">' .
            '<div id="' . e($id) . '-options">' .
            view('components/radios-inline', ['name' => $id . '-options', 'options' => $options, 'selected' => 'inline']) .
            '</div>' .
            '<div id="' . e($id) . '-inline">' .
            $submitter_text->edit($id, $name, $value, $tree) .
            '</div>' .
            '<div id="' . e($id) . '-shared" class="d-none">' .
            $xref_note->edit($id . '-select', $name, $value, $tree) .
            '</div>' .
            '</div>' .
            '<script>' .
            'document.getElementById("' . e($id) . '-shared").querySelector("select").disabled=true;' .
            'document.getElementById("' . e($id) . '-options").addEventListener("change", function(){' .
            ' document.getElementById("' . e($id) . '-inline").classList.toggle("d-none");' .
            ' document.getElementById("' . e($id) . '-shared").classList.toggle("d-none");' .
            ' const inline = document.getElementById("' . e($id) . '-inline").querySelector("textarea");' .
            ' const shared = document.getElementById("' . e($id) . '-shared").querySelector("select");' .
            ' inline.disabled = !inline.disabled;' .
            ' shared.disabled = !shared.disabled;' .
            ' if (shared.disabled) { shared.tomselect.disable(); } else { shared.tomselect.enable(); }' .
            '})' .
            '</script>';
    }
}
