let settings = c7tA;
console.log('LOADED');
document.load = function(){
    
    try {
        let form_ = document.querySelector(".wpcf7 form");
        let formData = new FormData(form);
        let id = formData.get('id');
        console.log(id);
      } catch (err) {
        console.warn(err);
      }
}

document.addEventListener(
  "wpcf7mailsent",
  function(event) {
    try {
      let form_ = document.querySelector(".wpcf7 form");
      let formData = new FormData(form);
      let id = formData.get('id');
      console.log(id);
    } catch (err) {
      console.warn(err);
    }
  },
  false
);
