// Function to search through the grid items
function searchGroups() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let groupCards = document.querySelectorAll('.group-card');
    
    groupCards.forEach(card => {
        let cardContent = card.querySelector('h3').textContent.toLowerCase() + " " +
                          card.querySelector('p').textContent.toLowerCase();
        if (cardContent.includes(input)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
