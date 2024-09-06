document.addEventListener("DOMContentLoaded", function() {
    const typingTextElement = document.getElementById("typingText");
    const originalText = "Dive into the Quantum Depths of EkiliSense Management: Unleash Productivity Potions for Supernatural Performance";
    let currentIndex = 0;

    function typingAnimation() {
      typingTextElement.textContent = originalText.substring(0, currentIndex);
      currentIndex++;

      if (currentIndex <= originalText.length) {
        setTimeout(typingAnimation, 100);
      }
    }

    typingAnimation();
  });
