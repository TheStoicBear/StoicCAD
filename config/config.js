// config.js
const config = {
    logo: "logo.png",
    buttonColor: "bg-blue-800",
    buttonHoverColor: "hover:bg-blue-700",
    textColor: "text-blue-300",
    iconClass: "fas fa-broadcast-tower",
    titleText: "StoicCAD Dispatch System",
    descriptionText: "Advanced dispatch solution designed to enhance operational efficiency and response times.",
    cards: [
        {
            id: "infoCard1",
            title: "Latest Dispatch Tools",
            visible: false
        },
        {
            id: "infoCard2",
            title: "Self Hosted",
            visible: false
        },
        {
            id: "infoCard3",
            title: "Open Source",
            visible: false
        }
    ]
};

function applyConfig() {
    document.getElementById('logo').src = config.logo;
    document.getElementById('mainTitle').textContent = config.titleText;
    document.getElementById('mainTitle').className += ` ${config.textColor}`;
    document.getElementById('descriptionText').textContent = config.descriptionText;
    document.getElementById('mainIcon').className = config.iconClass + " fa-4x mb-3";

    document.querySelectorAll('button').forEach(button => {
        button.className += ` ${config.buttonColor} ${config.buttonHoverColor}`;
    });

    config.cards.forEach(card => {
        const element = document.getElementById(card.id);
        element.querySelector('div').textContent = card.title;
        if (card.visible) {
            element.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', applyConfig);
function toggleInfoCards() {
    ['infoCard1', 'infoCard2', 'infoCard3'].forEach(cardId => {
        const card = document.getElementById(cardId);
        card.classList.toggle('hidden');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    applyConfig();
    document.getElementById('toggleInfoButton').addEventListener('click', toggleInfoCards);
});
