

<script setup>
    import { ref, onMounted } from 'vue';
    import BackendLayout from '@/Layouts/BackendLayout.vue';
    import { router, useForm, usePage } from '@inertiajs/vue3';
    import BarChart from '@/Components/Chart/BarChart.vue';
    import PieChart from '@/Components/Chart/PieChart.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import AlertMessage from '@/Components/AlertMessage.vue';
    import { displayResponse, displayWarning } from '@/responseMessage.js';

    const props = defineProps(['sessions']);


</script>

    <template>
        <BackendLayout>

                <div
                    class="flex items-center justify-between w-full text-gray-700 bg-gray-100 rounded-md shadow-md dark:bg-gray-800 dark:text-gray-200 shadow-gray-800/50">
                    <div>
                        <h1 class="p-4 text-xl font-bold dark:text-white">{{ $page.props.pageTitle }}</h1>
                    </div>
                    <div class="p-4 py-2">
                    </div>
                </div>

                <section class="w-full mt-4 transition duration-1000 ease-in-out">
                    <div class="grid gap-4 xs:grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        <div class="w-full h-20 pl-1 bg-yellow-400 rounded-lg shadow-md" v-for="session in sessions" :key="session.id" :value="session.id">
                            <a href="{{ route('backend.attendance.class.section') }}">
                                <div class="flex justify-between w-full h-full px-4 py-2 bg-white rounded-lg">
                                    <div class="my-auto">
                                        <p class="text-sm font-bold sm:text-md">{{ session.session_year}}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 mt-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="flex flex-col bg-white rounded-lg shadow md:col-span-2 md:row-span-2">
                        <div class="px-6 py-5 font-semibold border-b border-gray-100">The Attendance and left Employee per month
                        </div>
                        <div class="flex-grow p-4">
                            <div
                                class="flex items-center justify-center h-full px-4 py-16 text-3xl font-semibold text-gray-400 bg-gray-100 border-2 border-gray-200 border-dashed rounded-md">
                                <BarChart />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col bg-white rounded-lg shadow md:col-span-2 md:row-span-2">
                        <div class="px-6 py-5 font-semibold border-b border-gray-100">The Attendance and left Employee per month
                        </div>
                        <div class="flex-grow p-4">
                            <div
                                class="flex items-center justify-center h-full px-4 py-16 text-3xl font-semibold text-gray-400 bg-gray-100 border-2 border-gray-200 border-dashed rounded-md">
                                <PieChart />
                            </div>
                        </div>
                    </div>

                </section>
        </BackendLayout>
    </template>

