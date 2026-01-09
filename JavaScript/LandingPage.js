const scrollElement = window;

scrollElement.addEventListener('scroll', () => {

  const scrollTop = scrollElement.scrollY || scrollElement.scrollTop;

  const scrollThreshold = 100;

  const Navbar = document.getElementsByClassName('Navbar');

  if (scrollTop > scrollThreshold) {
    Navbar.classList.add('ScrolledNavbar');
  } else {
    Navbar.classList.remove('ScrolledNavbar');
  }
});