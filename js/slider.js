  //slider
  const imgPosition = document.querySelectorAll(".slide");
  const imgContainer = document.getElementById("sliderContainer");
  const dotItem = document.querySelectorAll(".dot");
  let imgNumber = imgPosition.length;
  let index = 0;

  imgPosition.forEach((image, i) => {
    image.style.left = i * 100 + "%";
    dotItem[i].addEventListener("click", () => slider(i));
  });

  function imgSlide() {
    index++;
    if (index >= imgNumber) index = 0;
    slider(index);
  }

  function slider(i) {
    imgContainer.style.left = "-" + i * 100 + "%";
    document.querySelector(".dot.active").classList.remove("active");
    dotItem[i].classList.add("active");
  }
  
  setInterval(imgSlide, 5000);