<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-lg text-gray-800 leading-tight">Your Pets</h3>
                    <ul id="pets-list">
                        <!-- Pets will be loaded here dynamically -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('/api/pets')
                .then(response => response.json())
                .then(data => {
                    const petsList = document.getElementById('pets-list');
                    if (data.length === 0) {
                        petsList.innerHTML = '<li>No pets yet</li>';
                    } else {
                        data.forEach(pet => {
                            const listItem = document.createElement('li');
                            listItem.textContent = pet.name;
                            petsList.appendChild(listItem);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching pets:', error);
                    const petsList = document.getElementById('pets-list');
                    petsList.innerHTML = '<li>Error loading pets</li>';
                });
        });
    </script>
</x-app-layout>
