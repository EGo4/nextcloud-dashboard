<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Patient Records</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead>
                <tr class="text-gray-400 font-semibold border-b border-gray-100">
                    <th class="pb-3 px-2">Name</th>
                    <th class="pb-3 px-2">Age</th>
                    <th class="pb-3 px-2">Disease</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($patients as $patient): ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="py-3 px-2 font-semibold text-gray-800"><?= $patient['name'] ?></td>
                    <td class="py-3 px-2"><?= $patient['age'] ?></td>
                    <td class="py-3 px-2"><?= $patient['disease'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>