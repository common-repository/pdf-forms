<div <?php echo $__data['popup_main_div_attributes'] ?>>
    <h2> <?php echo $__data['popup_main_div_header'] ?> </h2>
    <div class="select2-list-div">
        <div class="modal-select-div">
            <select class="select2-picker" style="width: 100%" name="fillable_template_list_fillable_template_list">
                <option value></option>
                <?php if (!empty($__data['default']) && !array_key_exists($__data['default']->id, $__data['list'])) { ?>
                    <option value="<?php echo  $__data['default']->id ?>" style="display: none" selected="selected">
                        <?php echo $__data['default']->name ?>
                    </option>
                <?php } ?>
                <?php foreach ($__data['list'] as $value => $title) : ?>
                    <option value="<?php echo $value ?>"
                        <?php if (is_object($__data['default']) && $__data['default']->id === $value ) : ?>
                            selected="selected"
                        <?php endif; ?>
                    >
                        <?php echo $title ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="modal-additional-button-div">
            <button type="button" id="first-additional-button">
                <?php echo $__data['first_additional_button_text'] ?>
            </button>
            <button type="button" id="second-additional-button">
                <?php echo $__data['second_additional_button_text'] ?>
            </button>
        </div>
    </div>
    <div class="modal-button-div">
        <button type="button" id="main-select-button">
            <?php echo $__data['button_text'] ?>
        </button>
    </div>
</div>

<div class="popup-main-div-picker">
    <a href="<?php echo $__data['open_popup_link_href'] ?>" class="popup-main-picker">
        <?php echo $__data['open_popup_link_text'] ?>
    </a>
</div>
