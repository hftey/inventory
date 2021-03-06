// DEMO JAVASCRIPT CODES (THEME CUSTOMIZATION)
// GET VALUES FROM COLOR PICKER AND SLIDER
// COLOR SWATCHES
// !You DO NOT NEED to include this file. This file is only for DEMO purpose

$(document).ready(function(){
  bgh = $(".menu-bg").css('backgroundColor');
  
  // main container bg#1
  $(".ch-menu-bg1").spectrum({
    color: bgh,
    change: function(color) {
      $(".menu-bg").css("background" , color.toHexString());
      containerbg1Value = color.toHexString();
      $('#containerbg1Value').html(containerbg1Value);
    }
  });

  // main container bg#2
  $(".ch-menu-bg2").spectrum({
    color: "#2AA4CF",
    change: function(color) {
      $(".bm-container").css("background" , color.toHexString());
      $("#login-container").css("border" , "1px solid" + color.toHexString() , "border-top" , "none");
      containerbg2Value = color.toHexString();
      $('#containerbg2Value').html(containerbg2Value);
    }
  });

  // slide-toggler bg
  $(".slide-toggler-button").spectrum({
    color: "#2693BA",
    change: function(color) {
      $(".side-menu-toggle").css({
        'background' : color.toHexString(),
        'background-image' : 'url(img/menu-toggle.png)',
        'background-repeat' : 'no-repeat',
        'background-position' : '15px 11px'
      });
      slideTogglerValue = color.toHexString();
      $('#slideTogglerValue').html(slideTogglerValue);
    }
  });

  // login-toggler bg
  $(".login-toggler-button").spectrum({
    color: "#2693BA",
    change: function(color) {
      $(".login-toggle").css({
        'background' : color.toHexString(),
        'background-image' : 'url(img/login.png)',
        'background-repeat' : 'no-repeat',
        'background-position' : '10px 11px'
      });
      loginTogglerValue = color.toHexString();
      $('#loginTogglerValue').html(loginTogglerValue);
    }
  });

  // sidebar bg
  $(".sidebar-bg").spectrum({
    color: "#333",
    change: function(color) {
      $(".hidden-menu").css("background" , color.toHexString());
      sidebarbgValue = color.toHexString();
      $('#sidebarbgValue').html(sidebarbgValue);
    }
  });

  // login-form bg
  $(".login-form-bg").spectrum({
    color: "#DDF0F9",
    change: function(color) {
      $("#login-container").css("background" , color.toHexString());
      loginformbgValue = color.toHexString();
      $('#loginformbgValue').html(loginformbgValue);
    }
  });

  // top-nav first bg
  $(".top-nav-first").spectrum({
    color: "#2AA4CF",
    change: function(color) {
    $(".top-nav > ul > li > a").css("background" , color.toHexString());
    bgcol = $(".top-nav > ul > li > a").css('backgroundColor');
    // Write color code
    var firstLevelBgValue = color.toHexString();
    $('#firstLevelBgValue').html(firstLevelBgValue);
    }
  });

  // top-nav first:hover bg
  $(".top-nav-first-hover").spectrum({
    color: "#104050",
    change: function(color) {
      // Write color code
      firstLevelHoverBgValue = color.toHexString();
      $('#firstLevelHoverBgValue').html(firstLevelHoverBgValue);
      // Set color code
      $(".top-nav > ul > li").hover(
        function () {
          el = $(this).find(' > a');
          el.css("background", color.toHexString());
        },
        function () {
          el.css("background", bgcol);
        }
      );
    }
  });

  // top-nav first level margin
  $("#first-level-margin").bind("slider:changed", function (event, data) {
    // The currently selected value of the slider
    $("#first-level-margin-value").html(data.value.toFixed(1));
    $(".top-nav > ul > li > a").css("margin-right" , data.value.toFixed(1) +"px");
  });

  // top-nav second bg
  $(".top-nav-second").spectrum({
    color: "#104050",
    showAlpha: true,
    preferredFormat: "rgb",
    showInput: true,
    change: function(color) {
      $(".top-nav > ul > li > ul > li > a").css("background" , color.toRgbString());
      bgcole = $(".top-nav > ul > li > ul > li > a").css( "background-color" );
      secondLevelBgValue = color.toRgbString();
      $('#secondLevelBgValue').html(secondLevelBgValue);
    }
  });

  // top-nav second:hover bg
  $(".top-nav-second-hover").spectrum({
    color: "#0A2832",
    showAlpha: true,
    preferredFormat: "rgb",
    showInput: true,
    change: function(color) {
      // Write color code
      secondLevelHoverBgValue = color.toRgbString();
      $('#secondLevelHoverBgValue').html(secondLevelHoverBgValue);
      // Set color code
      $(".top-nav > ul > li > ul > li").hover(
        function () {
          ele = $(this).find(' > a');
          ele2 = $(this).find(' > a').css('background')
          ele.css("background", color.toRgbString());
        },
        function () {
          ele.css("background", ele2);
        }
      );
    }
  });

  // top-nav second border 1
  $(".top-nav-second-border1").spectrum({
    color: "#144F65",
    change: function(color) {
      $(".top-nav > ul > li > ul > li > a").css("border-top" , "1px solid " + color.toHexString());
      secondBorder1Value = color.toHexString();
$('#secondBorder1Value').html(secondBorder1Value);
    }
  });

  // top-nav second border 2
  $(".top-nav-second-border2").spectrum({
    color: "#507270",
    change: function(color) {
      $(".top-nav > ul > li > ul > li > a").css("border-bottom" , "1px solid " + color.toHexString());
      secondBorder2Value = color.toHexString();
      $('#secondBorder2Value').html(secondBorder2Value);
    }
  });

  // top-nav third bg
  $(".top-nav-third").spectrum({
    color: "#2aa4cf",
    showAlpha: true,
    preferredFormat: "rgb",
    showInput: true,
    change: function(color) {
      $(".top-nav > ul > li > ul > li > ul > li > a").css("background" , color.toRgbString());
      bgcole = $(".top-nav > ul > li > ul > li >ul > li > a").css( "background-color" );
      thirdLevelBgValue = color.toRgbString();
      $('#thirdLevelBgValue').html(thirdLevelBgValue);
    }
  });

  // top-nav third:hover bg
  $(".top-nav-third-hover").spectrum({
    color: "#2699bf",
    showAlpha: true,
    preferredFormat: "rgb",
    showInput: true,
    change: function(color) {
      // Write color code
      thirdLevelHoverBgValue = color.toRgbString();
      $('#thirdLevelHoverBgValue').html(thirdLevelHoverBgValue);
      // Set color code
      $(".top-nav > ul > li > ul > li > ul > li").hover(
        function () {
          ele = $(this).find(' > a');
          ele2 = $(this).find(' > a').css('background')
          ele.css("background", color.toRgbString());
        },
        function () {
          ele.css("background", ele2);
        }
      );
    }
  });

  // top-nav third border 1
  $(".top-nav-third-border1").spectrum({
    color: "#2696BB",
    change: function(color) {
    $(".top-nav > ul > li > ul > li > ul > li > a").css("border-top" , "1px solid " + color.toHexString());
    thirdBorder1Value = color.toHexString();
    $('#thirdBorder1Value').html(thirdBorder1Value);
    }
  });

  // top-nav third border 2
  $(".top-nav-third-border2").spectrum({
    color: "#6AC4E1",
    change: function(color) {
      $(".top-nav > ul > li > ul > li > ul > li > a").css("border-bottom" , "1px solid " + color.toHexString());
      thirdBorder2Value = color.toHexString();
      $('#thirdBorder2Value').html(thirdBorder2Value);
    }
  });

  // top-nav forth bg
  $(".top-nav-forth").spectrum({
    color: "#62a6ca",
    showAlpha: true,
    preferredFormat: "rgb",
    showInput: true,
    change: function(color) {
      $(".top-nav > ul > li > ul > li > ul > li > ul > li > a").css("background" , color.toRgbString());
      bgcole = $(".top-nav > ul > li > ul > li > ul > li > ul > li > a").css( "background-color" );
      forthLevelBgValue = color.toRgbString();
      $('#forthLevelBgValue').html(forthLevelBgValue);
    }
  });

  // top-nav forth:hover bg
  $(".top-nav-forth-hover").spectrum({
    color: "#5b9cc8",
    showAlpha: true,
    preferredFormat: "rgb",
    showInput: true,
    change: function(color) {
      // Write color code
      forthLevelHoverBgValue = color.toRgbString();
      $('#forthLevelHoverBgValue').html(forthLevelHoverBgValue);
      // Set color code
      $(".top-nav > ul > li > ul > li > ul > li > ul > li").hover(
        function () {
          ele = $(this).find(' > a');
          ele2 = $(this).find(' > a').css('background')
          ele.css("background", color.toRgbString());
        },
        function () {
          ele.css("background", ele2);
        }
      );
    }
  });

  // top-nav forth border 1
  $(".top-nav-forth-border1").spectrum({
    color: "#5B9CC8",
    change: function(color) {
      $(".top-nav > ul > li > ul > li > ul > li > ul > li > a").css("border-top" , "1px solid " + color.toHexString());
      forthBorder1Value = color.toHexString();
      $('#forthBorder1Value').html(forthBorder1Value);
    }
  });

  // top-nav forth border 2
  $(".top-nav-forth-border2").spectrum({
    color: "#92BDDA",
    change: function(color) {
      $(".top-nav > ul > li > ul > li > ul > li > ul > li > a").css("border-bottom" , "1px solid " + color.toHexString());
      forthBorder2Value = color.toHexString();
      $('#forthBorder2Value').html(forthBorder2Value);
    }
  });
  
  // POPOVERS FOR DEMO
  $("#containerbg1").popover();
  $("#containerbg2").popover();
  $("#slideToggler").popover();
  $("#loginToggler").popover();
  $("#sidebarBg").popover();
  $("#loginFormBg").popover();
  $("#slideEffect").popover();
  $("#firstLevelBg").popover();
  $("#firstLevelHoverBg").popover();
  $("#firstLevelMargin").popover();
  $("#firstLevelSeperatorBg1").popover();
  $("#firstLevelSeperatorBg2").popover();
  $("#secondLevelBg").popover();
  $("#secondLevelHoverBg").popover();
  $("#secondLevelSeperatorBg1").popover();
  $("#secondLevelSeperatorBg2").popover();
  $("#thirdLevelBg").popover();
  $("#thirdLevelHoverBg").popover();
  $("#thirdLevelSeperatorBg1").popover();
  $("#thirdLevelSeperatorBg2").popover();
  $("#forthLevelBg").popover();
  $("#forthLevelHoverBg").popover();
  $("#forthLevelSeperatorBg1").popover();
  $("#forthLevelSeperatorBg2").popover();
  
    // S-W-I-T-C-H STYLES
  $('.swatch1').click(function(){
    $('head').append("<link href='css/swatch1.css' type='text/css' rel='stylesheet' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })

  $('.swatch2').click(function(){
    $('head').append("<link href='css/swatch2.css' type='text/css' rel='stylesheet' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })

  $('.swatch3').click(function(){
    $('head').append("<link href='css/swatch3.css' type='text/css' rel='stylesheet' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })

  $('.swatch4').click(function(){
    $('head').append("<link href='css/swatch4.css' type='text/css' rel='stylesheet' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })

  $('.swatch5').click(function(){
    $('head').append("<link href='css/swatch5.css' type='text/css' rel='stylesheet' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })

  $('.swatch6').click(function(){
    $('head').append("<link href='css/swatch6.css' type='text/css' rel='stylesheet' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })

  $('.swatch7').click(function(){
    $('head').append("<link href='css/swatch7.css' type='text/css' rel='stylesheet' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })

  $('.swatch8').click(function(){
    $('head').append("<link href='css/swatch8.css' type='text/css' rel='stylesheet' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })

  $('.swatch9').click(function(){
    $('head').append("<link href='css/swatch9.css' type='text/css' rel='stylesheet' id='black' />")
    $(".top-nav ul ul li:has(li)").find(' > a').css({'background-image' : 'url(img/icon-right-black.png)' , 'background-repeat' : 'no-repeat' , 'background-position' : '130px 15px'});
  })
  
})