<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    invoice: Object,
    transactions: Array,
    publicUrl: String,
});

function statusColor(status) {
    switch (status) {
        case "paid":
            return "bg-green-500";
        case "due":
            return "bg-yellow-500";
        case "unpaid":
            return "bg-red-500";
        case "cancelled":
            return "bg-gray-500";
        default:
            return "bg-neutral-400";
    }
}
function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(date);
}
function formatCurrency(value) {
    return Number(value).toFixed(2);
}
function newTab(e) {
    e.preventDefault();
    window.open(props.publicUrl, "_blank");
}

const showPaymentModal = ref(false);

function openPaymentModal() {
    showPaymentModal.value = true;
}

function closePaymentModal() {
    showPaymentModal.value = false;
}
function downloadPdf() {
    window.open(
        route("admin.invoice.download", props.invoice.invoice),
        "_blank"
    );
}
</script>

<template>
    <Head title="View Billing" />

    <div
        class="border rounded border-gray-300 shadow-sm p-5 bg-white w-4xl m-auto"
    >
        <div class="mt-5">
            <div class="flex justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold">Invoice</h1>
                    <h2 class="text-md mt-2 mb-5"># {{ invoice.invoice }}</h2>
                    <span
                        class="p-2 rounded text-white"
                        :class="statusColor(invoice.status)"
                    >
                        {{ invoice.status }}
                    </span>
                </div>
                <div class="text-end">
                    <p class="text-md font-semibold">Company Location:</p>
                    <address class="flex flex-col gap-2 mt-3">
                        <span v-if="$page.props.site?.company_location">{{
                            $page.props.site?.company_location
                        }}</span>
                        <span v-if="$page.props.site?.company_contact">
                            Contact No. :
                            <a
                                :href="`tel:${$page.props.site?.company_contact}`"
                                class="text-blue-600 hover:underline"
                            >
                                {{ $page.props.site?.company_contact }}
                            </a>
                        </span>
                        <span v-if="$page.props.site?.company_email">
                            Email :
                            <a
                                :href="`mailto:${$page.props.site?.company_email}`"
                                class="text-blue-600 hover:underline"
                            >
                                {{ $page.props.site?.company_email }}
                            </a>
                        </span>
                        <span v-if="$page.props.site?.company_web">
                            Web :
                            <a
                                :href="$page.props.site?.company_web"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-blue-600 hover:underline"
                            >
                                {{ $page.props.site?.company_web }}
                            </a>
                        </span>
                    </address>
                </div>
            </div>

            <div class="flex justify-between mb-3">
                <div>
                    <h2 class="font-semibold mb-2 text-sm">Invoice To.</h2>
                    <div class="flex flex-col gap-1.5">
                        <p>
                            <strong>Name:</strong>
                            {{ invoice.customer.name || "" }}
                        </p>
                        <p>
                            <strong>Address:</strong>
                            {{ invoice.address || "" }}
                        </p>
                        <p><strong>Phone:</strong></p>
                        <p>
                            <strong>Email:</strong>
                            {{ invoice.customer.email || "" }}
                        </p>
                    </div>
                </div>
                <div class="text-end flex flex-col gap-1.5">
                    <div class="flex justify-end">
                        <img
                            src="https://support.comitsbd.com/public//images/logo_1755536796.jpg"
                            class="h-10"
                            alt="site logo"
                        />
                    </div>
                    <p class="text-sm">
                        <strong>Invoice Date : </strong>
                        {{ formatDate(invoice.invoice_date) }}
                    </p>
                    <p class="text-sm">
                        <strong>Due Date : </strong>
                        {{ formatDate(invoice.updated_at) }}
                    </p>
                    <h2>
                        <strong class="text-2xl">Invoice Total : </strong
                        ><span class="text-lg"
                            >{{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.sub_total) }}</span
                        >
                    </h2>
                    <h2>
                        <strong class="text-2xl">Total Paid : </strong
                        ><span class="text-lg"
                            >{{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.paid) }}</span
                        >
                    </h2>
                    <h2>
                        <strong class="text-2xl">Total Due : </strong
                        ><span class="text-lg"
                            >{{ $page.props.site?.currency }}
                            {{
                                formatCurrency(invoice.sub_total - invoice.paid)
                            }}</span
                        >
                    </h2>
                </div>
            </div>

            <div class="flex justify-end" v-if="invoice.status != 'paid'">
                <div class="flex gap-2">
                    <select
                        class="p-1.5 rounded bg-teal-500 border border-teal-500 focus:border-teal-500"
                    >
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>

                    <button
                        class="p-2 rounded bg-teal-500 border-0"
                        @click="openPaymentModal"
                    >
                        Pay Now
                    </button>
                </div>
            </div>
        </div>

        <div>
            <h2 class="font-semibold mb-2">Invoice Items</h2>
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-3 py-2">#</th>
                        <th class="px-3 py-2">Item Name</th>
                        <th class="px-3 py-2">Price</th>
                        <th class="px-3 py-2">Qty</th>
                        <th class="px-3 py-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, $i) in invoice.items" :key="item.id">
                        <td class="px-3 py-1">
                            {{ $i + 1 }}
                        </td>
                        <td class="px-3 py-2">
                            {{ item.item_name }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(item.price) }}
                        </td>
                        <td class="px-3 py-2">
                            {{ item.qty }}
                        </td>
                        <td class="px-3 py-2 text-end">
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(item.price * item.qty) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="flex justify-end">
                <tbody>
                    <tr>
                        <td class="w-60 px-3 py-3">Subtotal</td>
                        <td class="w-60 py-3 px-3 text-end">
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.sub_total) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-40 py-3 px-3">Discount</td>
                        <td class="w-40 py-3 px-3 text-end">
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.total_discount) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-40 py-3 px-3">Tax</td>
                        <td class="w-40 py-3 px-3 text-end">
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.total_tax) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-40 py-3 px-3">Total</td>
                        <td class="w-40 py-3 px-3 text-end">
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.total) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-40 py-3 px-3">Round Value</td>
                        <td class="w-40 py-3 px-3 text-end">
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.round_value) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-40 py-3 px-3">Total Paid</td>
                        <td class="w-40 py-3 px-3 text-end">
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.paid) }}
                        </td>
                    </tr>
                    <tr class="text-2xl">
                        <td class="w-40 py-3 px-3">Amount Due</td>
                        <td class="w-40 py-3 px-3 text-end">
                            {{ $page.props.site?.currency }}
                            {{
                                formatCurrency(invoice.sub_total - invoice.paid)
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div>
            <h2 class="font-semibold mb-2">Related Transtiction</h2>
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-3 py-2">#</th>
                        <th class="px-3 py-2">Name</th>
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Invoice</th>
                        <th class="px-3 py-2">Account</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(t, index) in props.transactions"
                        :key="t.id"
                        class="border-b"
                    >
                        <td class="px-3 py-2">{{ index + 1 }}</td>
                        <td class="px-3 py-2">
                            {{ t.payee ?? "Customer" }}
                        </td>
                        <td class="px-3 py-2">
                            {{ new Date(t.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-3 py-2">
                            {{ t.invoice_id }}
                        </td>
                        <td class="px-3 py-2 capitalize">
                            {{ t.payment_method }}
                        </td>
                        <td class="px-3 py-2">
                            <span class="p-1 bg-green-300 rounded">{{ t.status }}</span>
                        </td>
                        <td
                            class="px-3 py-2 font-semibold text-green-600 text-end"
                        >
                            {{ $page.props.site?.currency }} {{ t.amount }}
                        </td>
                    </tr>

                    <tr v-if="props.transactions.length === 0">
                        <td colspan="7" class="py-3 text-red-500">
                            <p class="text-center">No transactions found.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="border rounded-sm bg-gray-100 mt-5 border-gray-300 p-3">
            <p class="mb-2 text-xl">Invoice Termes & conditions</p>
            <p v-html="invoice.terms"></p>
        </div>

        <div class="flex justify-end">
            <div class="flex gap-2 mt-4">
                <button
                    @click="downloadPdf"
                    class="p-2 rounded bg-teal-500 text-white"
                >
                    <i class="fa-regular fa-file-pdf"></i> Download Pdf
                </button>

                <button
                    type="button"
                    @click="newTab"
                    class="p-2 rounded bg-indigo-500 border-0 text-white"
                >
                    <i class="fa-solid fa-print"></i> Printable Version
                </button>
            </div>
        </div>

        <!-- Payment Modal -->
        <div
            v-if="showPaymentModal"
            class="fixed inset-0 bg-black/50 bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closePaymentModal"
        >
            <div class="bg-white p-5 rounded shadow-lg w-xl">
                <h2
                    class="text-lg font-bold mb-3 border-b border-b-gray-300 pb-2"
                >
                    Payment Details
                </h2>

                <div>
                    <h2 class="text-end">
                        <strong>Invoice Total : </strong>
                        <span>
                            {{ $page.props.site?.currency }}
                            {{ formatCurrency(invoice.sub_total) }}
                        </span>
                    </h2>
                    <div>
                        <h2 class="text-2xl font-bold">Invoice</h2>
                        <h3 class="text-md mt-2 mb-5">
                            # {{ invoice.invoice }}
                        </h3>
                        <div class="flex flex-col gap-2">
                            <p>Make a Payment to Our Bank Account</p>
                            <p>Bank Name: Butch Bangla Bank Limited</p>
                            <p>Account Name: Md. Rajib</p>
                            <p>Account Number: 107.101.51882</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button
                        @click="closePaymentModal"
                        class="px-3 py-1 bg-gray-300 rounded"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
