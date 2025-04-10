<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Love and Information</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #000;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 1600px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
        }

        .central-graphic {
            width: 80%;
            height: auto;
        }

        .columns-container {
            display: flex;
            gap: 20px;
            width: 80%;
        }

        .column {
            width: 50%;
        }

        .column img {
            width: 100%;
            height: auto;
        }

        .showtimes {
            display: none;
            width: 80%;
        }

        .tickets-container {
            width: 80%;
            text-align: center;
        }

        .tickets-container img {
            width: auto;
            height: auto;
            max-width: 100%;
        }

        @media (max-width: 1024px) {
            .columns-container {
                display: none;
            }

            .showtimes {
                display: block;
            }
        }
                
        .scroll-button {
            position: fixed;
            bottom: 3vh; /* Percentage of viewport height */
            right: 3vw;  /* Percentage of viewport width */
            padding: 1.5vh 2vw;
            background-color: #ff6b6b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Press Start 2P', cursive;
            font-size: clamp(14px, 1.5vw, 24px); /* Responsive font size */
            z-index: 1000;
            transition: all 0.3s ease;
            min-width: 200px;  /* Minimum width */
            min-height: 50px;  /* Minimum height */
            width: 15vw;       /* Responsive width */
            height: 8vh;       /* Responsive height */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            line-height: 1.5;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    
        .scroll-button:hover {
            background-color: #4ecdc4;
            transform: translateY(-2px);
        }
    
        /* Media queries for better responsiveness */
        @media (max-width: 768px) {
            .scroll-button {
                width: 30vw;
                font-size: clamp(12px, 3vw, 16px);
                min-width: 150px;
            }
        }
    
        @media (max-width: 480px) {
            .scroll-button {
                width: 40vw;
                font-size: clamp(10px, 4vw, 14px);
                min-width: 120px;
            }
        }
    
        .scroll-button.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Top graphic -->
        <img src="L&I-WebAssets/Central_graphic.svg" alt="central graphic for love and information presented by The Dan School" class="central-graphic">
        
        <!-- Columns (hidden on small screens) -->
        <div class="columns-container">
            <div class="column">
                <img src="L&I-WebAssets/Column1.svg" alt="Showtime column 1 from March 5th to 9th">
            </div>
            <div class="column">
                <img src="L&I-WebAssets/Column2.svg" alt="Showtime column 2 from March 10th to 16th">
            </div>
        </div>

        <!-- Showtimes (visible only on small screens) -->
        <img src="L&I-WebAssets/Showtimes.svg" alt="Showtimes from March 5th to 16th" class="showtimes">
        
        <!-- Tickets with link -->
        <div class="tickets-container">
            <a href="https://loveandinfo.com/Questionnaire">
                <img 
                    src="L&I-WebAssets/Tickets.svg" 
                    alt="Button to link to tickets" 
                    onmouseover="this.src='L&I-WebAssets/TicketYellow.svg'" 
                    onmouseout="this.src='L&I-WebAssets/Tickets.svg'"
                >
            </a>
        </div>
    </div>
    
    <button class="scroll-button" onclick="scrollToTickets()">Scroll to Tickets</button>

    <script>
        function checkWidth() {
            const columnsContainer = document.querySelector('.columns-container');
            const showtimes = document.querySelector('.showtimes');
            
            if (window.innerWidth <= 1024) {
                columnsContainer.style.display = 'none';
                showtimes.style.display = 'block';
            } else {
                columnsContainer.style.display = 'flex';
                showtimes.style.display = 'none';
            }
        }
        
        function scrollToTickets() {
            const ticketsSection = document.querySelector('.tickets-container');
            ticketsSection.scrollIntoView({  behavior: 'smooth' });
        }

        // Hide button when near bottom
        function toggleScrollButton() {
            const button = document.querySelector('.scroll-button');
            const ticketsSection = document.querySelector('.tickets-container');
            const ticketsPosition = ticketsSection.getBoundingClientRect().top;
            
            if (ticketsPosition < window.innerHeight) {
                button.classList.add('hidden');
            } else {
                button.classList.remove('hidden');
            }
        }

        window.addEventListener('load', checkWidth);
        window.addEventListener('resize', checkWidth);
        window.addEventListener('scroll', toggleScrollButton);
    </script>
</body>
</html>