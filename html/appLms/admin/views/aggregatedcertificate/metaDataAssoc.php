<?php


    cout(
        getTitleArea(Lang::t('_AGGREGATE_CERTIFICATES_ASSOCIATION_CAPTION'), 'certificate')
        .'<div class="std_block">'
            .Form::openForm('new_assign_step_0', 'index.php?r=alms/aggregatedcertificate/'. $operation)
                .Form::getHidden('id_certificate', 'id_certificate',  $id_certificate)
                .(isset($id_association) ? Form::getHidden('id_association', 'id_association',  $id_association) : '')
                .(isset($id_association) ? Form::getHidden('id_association', 'type_assoc',  $type_assoc) : '')
                .Form::openElementSpace()
                    .Form::getTextfield(Lang::t('_NAME'), 'title', 'title', '255', isset($associationMetadataArr['title']) ? $associationMetadataArr['title'] : '')
                    .Form::getSimpleTextarea(Lang::t('_DESCRIPTION'), 'description', 'description', isset($associationMetadataArr['description']) ? $associationMetadataArr['description'] : '')
                    .Form::getDropdown(Lang::t('_COURSE_TYPE','catalogue'),
                                         'type_assoc',
                                         'type_assoc',
                                         $assoc_types,
                                         $type_assoc,
                                         '',
                                         '',
                                         $html_before_select)
                .Form::closeElementSpace()
                .Form::openButtonSpace()
                    .Form::getButton('nextOperation', 'nextOperation', Lang::t('_NEXT'))
                    .($id_association != 0 ? Form::getButton('nextOperation', 'nextOperation', Lang::t('_SAVE')):"")
                    .Form::getButton('undo', 'undo_assign', Lang::t('_UNDO'))
                .Form::closeButtonSpace()
            .Form::closeForm()
        .'</div>'
    );

