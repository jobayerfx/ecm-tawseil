<x-cards.data :title="__('servermanager::app.billing')">
    <x-cards.data-row :label="__('servermanager::app.hosting.purchaseDate')" :value="$hosting->purchase_date ? $hosting->purchase_date->format(company()->date_format) : '--'" />
    <x-cards.data-row :label="__('servermanager::app.hosting.renewalDate')" :value="$hosting->renewal_date ? $hosting->renewal_date->format(company()->date_format) : '--'" />
    <x-cards.data-row :label="__('servermanager::app.hosting.plan')" :value="$hosting->billing_cycle" />
    <x-cards.data-row :label="__('servermanager::app.hosting.price')" :value="$hosting->annual_cost ? '$' . number_format($hosting->annual_cost, 2) : '--' " />
</x-cards.data>
