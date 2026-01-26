@if (module_enabled('Aitools') && (in_array('admin', user_roles()) || user()->permission('edit_aitools') == 'all'))
    <x-setting-menu-item :active="$activeMenu" menu="ai_tools_settings" :href="route('ai-tools-settings.index')"
                         :text="__('aitools::app.aiTools')"/>
@endif
