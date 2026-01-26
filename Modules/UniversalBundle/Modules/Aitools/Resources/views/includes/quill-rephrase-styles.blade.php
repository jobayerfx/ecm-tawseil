@if (module_enabled('Aitools') && (in_array('admin', user_roles()) || user()->permission('view_aitools') == 'all'))
<style>
    /* Rephrase button icon */
    .ql-rephrase {
        width: 28px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 5px;
        cursor: pointer;
        border: none;
        background: transparent;
        position: relative;
        vertical-align: middle;
    }
    .ql-rephrase svg {
        width: 18px;
        height: 18px;
    }
    .ql-rephrase .ql-fill {
        fill: #444;
    }
    .ql-rephrase .ql-stroke {
        fill: none;
        stroke: #444;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 2;
    }
    .ql-rephrase:hover {
        background-color: rgba(0,0,0,0.05);
        border-radius: 2px;
    }
    .ql-rephrase:hover .ql-fill {
        fill: #06c;
    }
    .ql-rephrase:hover .ql-stroke {
        stroke: #06c;
    }
    .ql-rephrase[title] {
        cursor: pointer;
    }
    .ql-rephrase.ql-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .ql-rephrase.ql-disabled:hover {
        background-color: transparent;
    }
</style>
@endif