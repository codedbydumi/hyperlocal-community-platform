document.addEventListener('DOMContentLoaded', function() {
    // Call updateCities when the DOM content is fully loaded
    updateCities();
});

function updateCities() {
    const district = document.getElementById('district').value;
    const citySelect = document.getElementById('city');
    const cities = {
        "colombo": [
            "Akarawita", "Angoda", "Athurugiriya", "Avissawella", "Bambalapitiya (Colombo 4)", "Batawala", 
            "Battaramulla", "Batugampola", "Bope", "Boralesgamuwa", "Borella (Colombo 8)", "Colpetty (Colombo 3)", 
            "Cinnamon Garden (Colombo 7)", "Deltara", "Dedigamuwa", "Habarakada", "Handapangoda", "Hanwella", 
            "Havelock Town (Colombo 5)", "Hewainna", "Hiripitya", "Hokandara", "Homagama", "Horagala", "Kalubowila", 
            "Kaduwela", "Kahawala", "Kalatuwawa", "Kosgama", "Kottegoda", "Kiriwattuduwa", "Kiribathgoda", "Malabe", 
            "Maharagama", "Madapatha", "Maradana (Colombo 10)", "Mattakkuliya (Colombo 15)", "Moratuwa", "Mount Lavinia", 
            "Mullegama", "Mulleriyawa New Town", "Mutwal (Colombo 15)", "Napawela", "Pethiyagoda", "Pettah (Colombo 11)", 
            "Pitipana Homagama", "Polgasowita", "Rajagiriya", "Ranala", "Ratmalana", "Siddamulla", "Slave Island (Colombo 2)", 
            "Sri Jayawardenepura", "Talawatugoda", "Waga", "Wathurugama", "Wellawatta", "Yatiyana (WP)"
        ],
        "gampaha": [
            "Akaragama", "Alawala", "Ambagaspitiya", "Ambepussa", "Andiambalama", "Attanagalla", "Badalgama", "Banduragoda",
            "Bokalagama", "Biyagama", "Biyagama IPZ", "Bope", "Borella", "Buthpitiya", "Dagonna", "Danowita", "Debahera",
            "Delgoda", "Delwagura", "Demalagama", "Demanhandiya", "Dewalapola", "Divulapitiya", "Divuldeniya", "Dompe",
            "Dunagaha", "Ekala", "Ellakkala", "Essella", "Gampaha", "Ganemulla", "GonawalaWP", "Heiyanthuduwa", "Henegama",
            "Hiswella", "Horagala", "Horampella", "Kalagedihena", "Kaleliya", "Kaluaggala", "Kandana", "Kapugoda", "Katana",
            "Katunayake", "Katunayake Air Force Camp", "Katunayake(FTZ)", "Katuwellegama", "Kelaniya", "Kimbulapitiya",
            "Kirindiwela", "Kiriwattuduwa", "Kiribathgoda", "Kitalawalana", "Kitulwala", "Kochchikade", "Kotadeniyawa",
            "Kotugoda", "Kumbaloluwa", "Loluwagoda", "Mabodale", "Madelgamuwa", "Makewita", "Malwana", "Mandawala",
            "Marandagahamula", "Mirigama", "Mithirigala", "Minuwangoda", "Muddaragama", "Mudungoda", "Naranwala", "Nawana",
            "Nedungamuwa", "Negombo", "Nikahetikanda", "Nittambuwa", "Niwandama", "Pallewela", "Pamunugama", "Pamunuwatta",
            "Pasyala", "Peliyagoda", "Pepiliyawala", "Pethiyagoda", "Polgasowita", "Polpithimukulana", "Pugoda", "Radawadunna",
            "Radawana", "Raddolugama", "Ragama", "Ruggahawila", "Rukmale", "Seeduwa", "Siyambalape", "Talahena", "Thimbirigaskatuwa",
            "Tittapattara", "Udathuthiripitiya", "Udugampola", "Uggalboda", "Urapola", "Uswetakeiyawa", "Veyangoda", "Walpita",
            "Walpola (WP)", "Weweldeniya", "Wattala", "Yakkala"
        ]
    };

    // Clear existing options
    citySelect.innerHTML = '<option value="">--All Cities--</option>';

    // Add new options based on selected district
    if (district && cities[district]) {
        cities[district].forEach(function(city) {
            const option = document.createElement('option');
            option.value = city.toLowerCase().replace(/\s/g, '_');
            option.textContent = city;
            citySelect.appendChild(option);
        });
    }
}


// Helper function to add options to select elements
function addOption(selectElement, value, text) {
    var option = document.createElement("option");
    option.value = value;
    option.textContent = text;
    selectElement.appendChild(option);
}

// Initialize dropdowns based on current selections
document.addEventListener("DOMContentLoaded", function() {
    // Call update functions if values are already selected (e.g., after form submission)
    if (document.getElementById("district").value) {
        updateCities();
        
        // Restore selected city if it exists
        var cityValue = document.getElementById("city").getAttribute("data-selected") || "";
        if (cityValue) {
            document.getElementById("city").value = cityValue;
            updateSuburbs();
            
            // Restore selected suburb if it exists
            var suburbValue = document.getElementById("suburb").getAttribute("data-selected") || "";
            if (suburbValue) {
                document.getElementById("suburb").value = suburbValue;
            }
        }
    }
    
    // Make sure the item type dropdown is initialized correctly
    var itemTypeSelect = document.getElementById("item_type");
    if (itemTypeSelect) {
        // The selection is already handled by PHP in the select element
        console.log("Item type filter initialized");
    }
});