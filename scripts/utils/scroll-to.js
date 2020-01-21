
const requestAnimFrame = (function() {return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function(callback) {window.setTimeout(callback, 1000 / 60)}})()

const easeInOutQuad = function(t, b, c, d) {
  t /= d / 2
  if (t < 1) return c / 2 * t * t + b
  t--
  return -c / 2 * (t * (t - 2) - 1) + b
}

const animatedScrollTo = function(element, offset, duration, callback) {
  const to = window.pageYOffset + element.getBoundingClientRect().top - offset
  const start = window.pageYOffset


  const change = to - start


  const animationStart = Date.now()
  let animating = true
  let lastpos = null

  var animateScroll = function() {
    if (!animating) {
      return
    }
    requestAnimFrame(animateScroll)
    const now = Date.now()
    const val = Math.floor(easeInOutQuad(now - animationStart, start, change, duration))
    if (lastpos) {
      if (lastpos === window.pageYOffset) {
        lastpos = val
        window.scrollTo(0, val)
      } else {
        animating = false
        if (callback) { callback() }
      }
    } else {
      lastpos = val
      window.scrollTo(0, val)
    }
    if (now > animationStart + duration) {
      window.scrollTo(0, to)
      animating = false
      if (callback) { callback() }
    }
  }
  requestAnimFrame(animateScroll)
}

export default animatedScrollTo
