<?php
/**
 * WPCleanAdmin Settings Field Renderers
 *
 * 承载各类型设置字段的渲染方法，从 Settings_Fields 主类抽取。
 *
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin\Modules\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 字段渲染器类
 */
class Settings_Field_Renderers {

    /**
     * Render text field
     */
    public function render_text_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <input type="text" id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" value="<?php echo \esc_attr( $value ); ?>" class="regular-text" />
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render textarea field
     */
    public function render_textarea_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <textarea id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" class="large-text code" rows="5"><?php echo \esc_textarea( $value ); ?></textarea>
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <label for="<?php echo \esc_attr( $field_id ); ?>">
            <input type="checkbox" id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" value="1" <?php \checked( $value ); ?> />
            <?php echo \wp_kses_post( $desc ); ?>
        </label>
        <?php
    }

    /**
     * Render radio field
     */
    public function render_radio_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $options  = isset( $args['options'] ) ? $args['options'] : array();
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <fieldset>
            <legend class="screen-reader-text"><?php echo \esc_html( $field_id ); ?></legend>
            <?php foreach ( $options as $option_value => $option_label ) : ?>
                <label>
                    <input type="radio" name="<?php echo \esc_attr( $field_id ); ?>" value="<?php echo \esc_attr( $option_value ); ?>" <?php \checked( $value, $option_value ); ?> />
                    <?php echo \esc_html( $option_label ); ?>
                </label><br />
            <?php endforeach; ?>
            <?php if ( $desc ) : ?>
                <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
            <?php endif; ?>
        </fieldset>
        <?php
    }

    /**
     * Render select field
     */
    public function render_select_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $options  = isset( $args['options'] ) ? $args['options'] : array();
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <select id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>">
            <?php foreach ( $options as $option_value => $option_label ) : ?>
                <option value="<?php echo \esc_attr( $option_value ); ?>" <?php \selected( $value, $option_value ); ?>><?php echo \esc_html( $option_label ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render number field
     */
    public function render_number_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $min      = isset( $args['min'] ) ? $args['min'] : '';
        $max      = isset( $args['max'] ) ? $args['max'] : '';
        $step     = isset( $args['step'] ) ? $args['step'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <input type="number" id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" value="<?php echo \esc_attr( $value ); ?>" class="small-text" min="<?php echo \esc_attr( $min ); ?>" max="<?php echo \esc_attr( $max ); ?>" step="<?php echo \esc_attr( $step ); ?>" />
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render color field
     */
    public function render_color_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <input type="color" id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" value="<?php echo \esc_attr( $value ); ?>" />
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render date field
     */
    public function render_date_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <input type="date" id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" value="<?php echo \esc_attr( $value ); ?>" class="regular-text" />
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render email field
     */
    public function render_email_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <input type="email" id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" value="<?php echo \esc_attr( $value ); ?>" class="regular-text" />
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render URL field
     */
    public function render_url_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <input type="url" id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" value="<?php echo \esc_attr( $value ); ?>" class="regular-text" />
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render password field
     */
    public function render_password_field( $args ) {
        $field_id = isset( $args['id'] ) ? $args['id'] : '';
        $value    = isset( $args['value'] ) ? $args['value'] : '';
        $desc     = isset( $args['description'] ) ? $args['description'] : '';
        ?>
        <input type="password" id="<?php echo \esc_attr( $field_id ); ?>" name="<?php echo \esc_attr( $field_id ); ?>" value="<?php echo \esc_attr( $value ); ?>" class="regular-text" />
        <?php if ( $desc ) : ?>
            <p class="description"><?php echo \wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
        <?php
    }
}
