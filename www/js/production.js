/**
 * 1chan.ru
 */
(function(){
	var x = {
		socket:     new Dklab_Realplexor("http://pipe.1chan.ru/", "1chan_"),
		processors: [],
		addPageProcessor: function(path, processor) {
			path = path instanceof Array ? path : [path];

			for(var i in path)
				this.processors.push({path: path[i], processor: processor});
		},
		callPageProcessors: function(path, params) {
			for(var i in this.processors)
				if ((this.processors[i]["path"] instanceof RegExp && this.processors[i]["path"].test(path)))
					this.processors[i]["processor"].call(window, this.processors[i]["path"].exec(path), params);
				else if (this.processors[i]["path"] == path)
					this.processors[i]["processor"].call(window, null, params);
		},
		subscribe: function(channel, event, callback) {
			this.socket.subscribe(channel, function(message, id){
				if (message.event == event)
					callback(message.data);
			});
		}
	};

	/**
	 * ----------------------------------------------- *
	 */
	x.addPageProcessor("news/add", function() {
		var validatePost = function(callback) {
			$("#blog_form_error").html("");
			$("#placeholder_preview").html("");
			$("#blog_form input, #blog_form textarea").removeClass("g-input-error");

			$.post(
				"http://"+ location.host +"/news/add/validate/",
				{
					"category":   $("#blog_form [name=category]").val(),
					"link":       $("#blog_form [name=link]").val(),
					"title":      $("#blog_form [name=title]").val(),
					"text":       $("#blog_form [name=text]").val(),
					"text_full":  $("#blog_form [name=text_full]").val()
				},
				function(result, status) {
					if (status != "error") {
						if (result.isValid != true)
						{
							var error_strings = [];
							for (var field in result.validationResults)
							if (result.validationResults.hasOwnProperty(field))
							{
								error_strings.push(result.validationResults[field]);
								$("#blog_form [name="+ field +"]").addClass("g-input-error");
							}
							$("#blog_form_error").html(error_strings.join(", "));
							return false;
						}
						callback.call();
					}
				},
				"json"
			);
		};

		$("#blog_form_preview_button")
				.attr("disabled", "")
				.click(function() {
					validatePost(function()  {
						$.post(
							"http://"+ location.host +"/news/add/preview/",
							{
								"category":   $("#blog_form [name=category]").val(),
								"link":       $("#blog_form [name=link]").val(),
								"title":      $("#blog_form [name=title]").val(),
								"text":       $("#blog_form [name=text]").val(),
								"text_full":  $("#blog_form [name=text_full]").val()
							},
							function (result, status) {
								if (status != "error") {
									$("#placeholder_preview").html(template("template_preview", {
										"title":    result["title"],
										"icon":     result["icon"],
										"text":     result["text"] + result["text_full"],
										"category": result["category"]
									}));
								}
							},
							"json"
						);
					});
				});

		$.getJSON("http://"+ location.host +"/news/cat/", function(data, status) {
			$( "input[name=category]" )
					.autocomplete({
						minLength: 0,
						source: data,
						focus: function( event, ui ) {
							$( "input[name=category]" ).val( ui.item.title +" <"+ ui.item.value +">" );
							return false;
						},
						select: function( event, ui ) {
							$( "input[name=category]" ).val( ui.item.title +" <"+ ui.item.value +">" );

							return false;
						}
					})
					.bind("click", function() {
						$(this).autocomplete("search");
					})
					.data( "autocomplete" )._renderItem = function( ul, item ) {
						return $( "<li></li>" )
							.css("fontSize", "0.7em")
							.data( "item.autocomplete", item )
							.append( "<a><b>" + item.title + "</b><br />" + item.desc + "</a>" )
							.appendTo( ul );
					};
		});

		$("#blog_form input[name=title]")
			.bind("keyup", function() {
				$("#blog_form_title_length")
					.html(ending($(this).val().length, "символ", "символа", "символов"));
			});

		$("#blog_form input[type=submit]").click(function() {
			validatePost(function() {
				x.socket.unsubscribe("new_posts");
				x.socket.execute();
				$("#blog_form").submit();
			});
			return false;
		});

		$(".js-category-pastelink").click(function() {
		    var code = $(this).attr("name");
		    $("#blog_form input[name=category]").val(code);
		});

		$(".js-homeboard-link").click(function() {
			$(".js-homeboard-select").toggle();
		});

		$(document.body).bind("mouseup", function() {
			$(".js-homeboard-select").hide();
		});

		$(".js-homeboard-select").bind("mouseup", function(e) {
			e.stopPropagation();
		});
		
		$(".js-homeboard-select-link").click(function(e) {
			var board = $(this).attr("name");
			$("input[name=homeboard]").val(board);
			$.cookie("homeboard", board, {expires: 356, path: "/"});

			$(".js-homeboard-icon").attr("src", $("img", this).attr("src"));
			$(".js-homeboard-select").hide();
		});
		
		x.subscribe("new_posts", "add_post", function(data) {
			$("#blog_form_last_posts").show();
			$("#placeholder_last_posts").append($(template("template_last_posts", data)));
		});

		x.addPageProcessor(":moderator", function() {
			$("#blog_form_moderation").removeClass("g-hidden");
		});
	});

	x.addPageProcessor(
		["news/fav", "news/search", /^news\/?(\d+)?$/, /^news\/all/, /^news\/cat/, /^news\/hidden/],
		function() {
			x.subscribe("posts", "add_post_comment", function(data) {
				$("#post_"+ data.id +"_info .js-comments").addClass("g-bold").html(data.count);
			});
			x.subscribe("posts", "remove_post_comment", function(data) {
				$("#post_"+ data.id +"_info .js-comments").addClass("g-bold").html(data.count);
			});
			x.subscribe("posts", "rate_post", function(data) {
				$("#post_"+ data.id +"_info .js-rate")
					.removeClass("g-red g-green").addClass((data.rate >= 0) ? "g-green" : "g-red")
					.html(data.rate);
			});
		}
	);

	x.addPageProcessor(
		["news/fav", "news/search", /^news\/?(\d+)?$/, /^news\/all/, /^news\/cat/, /^news\/res/, /^news\/hidden/],
		function() {
		    $(window)
		        .bind("keyup", function(e) {
				    if ((e.ctrlKey || e.metaKey) && (e.keyCode==38)) {
					    $(window).scrollTo(0, 500);
				    }

				    if ((e.ctrlKey || e.metaKey) && (e.keyCode==40)) {
				    	var comment = $(".b-comment.m-new").get(0) || $("#comment_form").get(0);

				    	if (comment) {
					   		$(window).scrollTo(comment, 500);
					   		$(comment).removeClass("m-new");
					   	} else {
					   		$(window).scrollTo("100%", 500);
					   	}
				    }

				    if ((e.ctrlKey || e.metaKey) && (e.keyCode==39)) {
					    location.href = "http://"+ location.host +"/news/all/new/";
				    }

				    if ((e.ctrlKey || e.metaKey) && (e.keyCode==37)) {
				       history.go(-1);
				    }
			    });

			$(".js-favorite-button").click(function() {
				var img = $("img", this);
				$.getJSON(this.href, function(data, status) {
					if (data.favorite == true)
						$(img).attr("src", "http://"+ location.host +"/ico/favorites-true.png");
					else
						$(img).attr("src", "http://"+ location.host +"/ico/favorites-false.png");
				});
				return false;
			});
			$(".js-rate-up-button, .js-rate-down-button").click(function() {
				var href = this.href, jsrate = $(".js-rate", $(this).closest(".b-blog-entry_b-info"));
				$.getJSON(href, function(data, state) {
					if (data.rate == false) {
						jsrate.addClass("g-disabled");
						setTimeout(function() { jsrate.removeClass("g-disabled"); }, 1000 );
					}
					if (data.rate == "captcha")
						location.href = href;
				});
				return false;
			});
			x.addPageProcessor(":moderator", function(path, params) {
				var current_id = null;
				jQuery.support.opacity = true;

                $(".b-blog-entry_b-info").each(function() {
                    var id = $(this).attr("id").match(/(\d+)/)[1];
                    
                    $("<input type='checkbox' class='js-post-group' />")
                        .attr("name", id)
                        .css({"vertical-align":"middle"})
                        .prependTo(this);
                });
                
                $(window).bind("keyup", function(e) {
                    if (e.shiftKey && (e.keyCode==68)) {
                        if (confirm("Вы уверены, что хотите удалить посты?")) {
					        var why = prompt("Введите причину модерации:", "мусор (читайте правила)");
					        $(".js-post-group:checked").each(function(el, ind) {
					            var id = $(this).attr("name");
    						    
    						    setTimeout((function(id, why) {
    						        return function() { $.getJSON("http://"+ location.host +"/mod/hiddenPost/"+ id +"/?why="+ encodeURIComponent(why)); };
    						    })(id, why), 1000 * ind);
    						});
					    }
					    return false;
                    }
                    
                    e.stopPropagation();
                });
                
				$("#mod_category").click(function() {
				    var cat = prompt('Введите id категории:', '');
					$.getJSON("http://"+ location.host +"/mod/categoryPost/"+ current_id +"/?cat="+ encodeURIComponent(cat));
					$(".b-mod-toolbar").addClass("g-hidden");
					current_id = null;
					return false;
				});
				$("#mod_pinned").click(function() {
					$.getJSON("http://"+ location.host +"/mod/pinnedPost/"+ current_id +"/");
					$(".b-mod-toolbar").addClass("g-hidden");
					current_id = null;
					return false;
				});
				$("#mod_rated").click(function() {
					$.getJSON("http://"+ location.host +"/mod/ratedPost/"+ current_id +"/");
					$(".b-mod-toolbar").addClass("g-hidden");
					current_id = null;
					return false;
				});
				$("#mod_rateable").click(function() {
					$.getJSON("http://"+ location.host +"/mod/rateablePost/"+ current_id +"/");
					$(".b-mod-toolbar").addClass("g-hidden");
					current_id = null;
					return false;
				});
				$("#mod_closed").click(function() {
					$.getJSON("http://"+ location.host +"/mod/closedPost/"+ current_id +"/");
					$(".b-mod-toolbar").addClass("g-hidden");
					current_id = null;
					return false;
				});
				if (params == true) {
				    $("#mod_remove").click(function() {
					    if (confirm("Вы уверены, что хотите удалить пост "+ current_id +"?")) {
					        var why = prompt("Введите причину модерации:", "мусор (читайте правила)");
						    $.getJSON("http://"+ location.host +"/mod/hiddenPost/"+ current_id +"/?why="+ encodeURIComponent(why));
						    $(".b-mod-toolbar").addClass("g-hidden");
						    current_id = null;
					    }
					    return false;
				    });
			    } else {
			        $("#mod_remove").hide();
			    }
				$(".b-blog-entry_b-info").bind("dblclick", function() {
					var current_id = $(this).attr("id").substring(5).substring(0, -5); console.log(current_id);
					$.getJSON("http://"+ location.host +"/mod/hiddenPost/"+ current_id +"/?why="+ encodeURIComponent("Мусор (читайте правила)"));
				});
				$(".js-moderator-button").removeClass("g-hidden").click(function(e) {
					current_id = $("img", this).attr("alt");
					$.getJSON("http://"+ location.host +"/mod/getPost/"+ current_id +"/" , function(data, status) {
						if (status != "error" && data) {
							$("#mod_pinned img")  .css({opacity: data.pinned == true ?   1 : .5});
							$("#mod_rated img")   .css({opacity: data.rated == true ?    1 : .5});
							$("#mod_rateable img").css({opacity: data.rateable == true ? 1 : .5});
							$("#mod_closed img")  .css({opacity: data.closed == true ?   1 : .5});
							$(".b-mod-toolbar")   .css({top: e.pageY + 8, left: e.pageX + 8}).removeClass("g-hidden");
						}
					});
					e.stopPropagation();
					return false;
				});
				$(document.body).click(function() {
					$(".b-mod-toolbar").addClass("g-hidden");
					current_id = null;
				});
			});
		}
 	);

	x.addPageProcessor("news/all", function() {
		x.subscribe("posts", "add_post", function(data) {
			$("#post_notify").show("fade");
		});

		var hidePostList = {};
	    (function() {
		    var cookieList = $.cookie("hide");
		    if (cookieList !== null) {
			    var list = cookieList.split(",");
			    for(var i in list) {
				    hidePostList[list[i]] = true;
			    }
		    }
	    })();

	    var toggleHidePost = function(id) {
		    if (id in hidePostList)
			    delete hidePostList[id];
		    else
			    hidePostList[id] = true;

		    var strings = [], i;

		    for(i in hidePostList)
			    if(hidePostList.hasOwnProperty(i))
				    strings.unshift(i);

		    $.cookie("hide", strings.slice(0, 31).join(","), {expires: 7, path: "/"});

		    return (id in hidePostList) ? true : false;
	    };

	    $(".js-hide-link").each(function() {
	        var id = $("img", this).attr("alt"), node = $("#post_"+ id);

	        $(this).click(function() {
	            if (toggleHidePost(id)) {
			        $(node).addClass("m-hide");
				    $(".b-blog-entry_b-header a", node).one('click', function(e) {
					    toggleHidePost(id);
					    $(node).removeClass("m-hide");
					    e.stopPropagation();
					    e.preventDefault();
				    });
			    }
			});

			if (id in hidePostList) {
			    $(node).addClass("m-hide");
			    $(".b-blog-entry_b-header a", node).one('click', function(e) {
				    toggleHidePost(id);
				    $(node).removeClass("m-hide");
				    e.stopPropagation();
				    e.preventDefault();
			    });
		    }

		    $(this).removeClass("g-hidden");
	    });
	});

	x.addPageProcessor(/^news\/?(\d+)?$/, function() {
		x.subscribe("posts", "rated_post", function(data) {
			$("#post_notify").show("fade");
		});
	});

	x.addPageProcessor(/^news\/res\/(\d+)/, function(match) {
		var id = match[1],
		    addComment = function() {  
			$("#comment_form_error").html("");
			$("#comment_form input[type=submit]").attr("disabled", "disabled");
			$.post(
				"http://"+ location.host +"/news/res/"+ id +"/add_comment/",
				{
					"post_id":   $("#comment_form input[name=post_id]").val(),
					"text":      $("#comment_form textarea").val(),
					"homeboard": $("#comment_form input[name=homeboard]").val()
				},
				function(result, status) {
			        $("#comment_form input[type=submit]").attr("disabled", "");
					if (status != "error") {
						if (result.captcha == true) {
							$("#comment_form").submit();
						}

						if (result.isValid != true) {
							var error_strings = [];
							for (var field in result.validationResults)
							if (result.validationResults.hasOwnProperty(field))
								error_strings.push(result.validationResults[field]);
							$("#comment_form_error").html(error_strings.join(", "));
							return false;
						}
						$("#comment_form textarea").val("");
					}
				},
				"json"
			);
		};
		var writing, waiting, writingTimeout = null, _title = document.title,
		   statusReading = function() {
			$.getJSON("http://"+ location.host +"/news/res/"+ id +"/stats/?writing=0");
			writing = false;
		};
		var scrollingInterval = null,
		    scrollEnable = function() {
			if (scrollingInterval) clearInterval(scrollingInterval);
			var cacheOffsetHeight = parseInt(document.body.offsetHeight);
			scrollingInterval = setInterval(function() {
				if (parseInt(document.body.offsetHeight) != cacheOffsetHeight)
					window.scrollBy(0, parseInt(document.body.offsetHeight) - cacheOffsetHeight);

				cacheOffsetHeight = parseInt(document.body.offsetHeight);
			}, 15);
		    },
		    scrollDisable = function() {
			if (scrollingInterval) clearInterval(scrollingInterval);
		    };
		var hash = "";
		setInterval(function() {
			if (window.location.hash != hash) {
				hash = window.location.hash;
				$(".b-comment").removeClass("m-selected");
				$("#comment_"+hash.substring(1)).addClass("m-selected");
			}
		}, 100);
		var previewTimeout, previewActive, previewActiveLink, commentPreview = function(node, clone) {
			clone = clone || false;
			$(".js-cross-link", node).mouseover(function(e) {
				if (!$(this).data("preview_open"))
				{
					var nm = $(this).attr("name").substring(0, 4);
					if (nm && nm != "news")
						return;

					if (!clone && previewActiveLink) {
						$(".b-comment.m-tip").remove();
						$(previewActiveLink).data("preview_open", false);
						previewActiveLink = this;
					} else if (!previewActiveLink)
						previewActiveLink = this;

					previewActive     = true;
					previewTimeout    = clearTimeout(previewTimeout);

					var id = $(this).text().replace(/\D/g, ""), el = $("#comment_" + id);

					if (el.length != 0) {
						var tip = $(el).clone()
						               .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
						               .addClass("m-tip")
							       	   .attr('id', '')
						               .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});
						$(document.body).append(tip);
						tip.slideUp(0).slideDown(300);
						$(this).data("preview_open", true);
						commentPreview(tip, true);
					} else {
						var link_ = this;
						$.getJSON("http://"+ location.host +"/news/last_comments/", {id: id}, function(data, status) {
							if (status != "error" && data != false) {
								var tip = $(template("template_comment", data))
									       .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
									       .addClass("m-tip")
									       .attr('id', '')
									       .css({display:'block', width: data.post_preview ? '520px' : '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});

								if (data.post_preview) tip.addClass("m-post-preview").find(".js-comment-id").html('<a href="http://'+ location.host +'/news/res/'+ data.post_id +'/">'+ data.post_title +'</a> (<em>открывающий пост</em>)')
								else tip.find(".js-comment-id").prepend('<a href="http://'+ location.host +'/news/res/'+ data.post_id +'/">'+ data.post_title +'</a> ');

								$(document.body).append(tip);
								tip.slideUp(0).slideDown(300);
								$(link_).data("preview_open", true);
								commentPreview(tip, true);
							}
						});
					}
				}
				e.stopPropagation();
			});
		};
		$(document.body).mouseover(function(e) {
			if (!previewTimeout && previewActive) {
				previewTimeout = setTimeout(function() {
					$(".b-comment.m-tip").remove();
					$(previewActiveLink).data("preview_open", false);
					previewActiveLink = null;
					previewActive = false;
				}, 400);
			}
		});

		var contentBottom = function() {
			var el = $(".b-blog-entry"), offset = el.offset(), outHeight = el.outerHeight(true);
			return offset.top + outHeight;
		}(), headerShown = false, headerEl = $(".b-blog-entry_b-header").clone().css({"position":"fixed", "top": 2}).addClass("m-floating");
		$(".b-blog-entry").append(headerEl);
		headerEl.hide();
		
		$(window).bind('scroll', function() {
			if ((document.body.scrollTop || document.documentElement.scrollTop) > contentBottom) {
				if (!headerShown) {
					headerShown = true;
					headerEl.show();
				}
			} else {
				if (headerShown) {
					headerShown = false;
					headerEl.hide();
				}
			}
		});
		$(".b-comment-form_b-uplink a").click(function() {
			$(window).scrollTo(0, 0);
		});
		if ($(".b-comment.m-new").length != 0) {
			var comment_id = $(".b-comment.m-new").eq(0).find(".js-paste-link").attr("name");
			$("#new_comments_link").removeClass("g-disabled").click(function() {
				location.hash = comment_id;
			});
			if (location.hash == "#new") {
			    location.hash = comment_id;
			}
		}
		$(".js-post-id-link").click(function() {
			insertText(">>" + id);
			return false;
		});
		$(".js-paste-link").live("click", function() {
			insertText(">>" + $(this).attr("name"));
			return false;
		});

		$(".js-back-link").click(function() {
			history.go(-1);
			return false;
		});

		$(".js-next-link").click(function() {
			location.href = "http://"+ location.host +"/news/all/new/";
			return false;
		});
		
		$(".js-homeboard-link").click(function() {
			$(".js-homeboard-select").toggle();
		});

		$(document.body).bind("mouseup", function() {
			$(".js-homeboard-select").hide();
		});

		$(".js-homeboard-select").bind("mouseup", function(e) {
			e.stopPropagation();
		});
		
		$(".js-homeboard-select-link").click(function(e) {
			var board = $(this).attr("name");
			$("input[name=homeboard]").val(board);
			$.cookie("homeboard", board, {expires: 356, path: "/"});

			$(".js-homeboard-icon").attr("src", $("img", this).attr("src"));
			$(".js-homeboard-select").hide();
		});

		$("#comment_form input[type=submit]").click(function() {
			addComment();
			return false;
		});
		$("#comment_form_text")
			.bind("keyup", function(e) {
				if (!writing) {
					$.getJSON("http://"+ location.host +"/news/res/"+ id +"/stats/?writing=1");
					writing = true;
				}

				if (writingTimeout) clearTimeout(writingTimeout);
				writingTimeout = setTimeout(statusReading, 5000);
				e.stopPropagation();
			})
			.bind("focus", function() {
				scrollEnable();
			})
			.bind("blur", function() {
				$.getJSON("http://"+ location.host +"/news/res/"+ id +"/stats/?writing=0");
				writing = false;
				scrollDisable();
			})
			.bind("keyup", function(e) {
				if ((e.ctrlKey || e.metaKey) && (e.keyCode==10 || e.keyCode==13)) {
					$("#comment_form input[type=submit]").click();
				}
			});
		$(window).bind("blur", function() {
				$(".b-comment.m-new").removeClass("m-new");
				waiting = true;
			})
			.bind("focus", function() {
				waiting = false;
				document.title = _title;
			});
		x.subscribe("post_"+ id, "stats_updated", function(data) {
			$("#post_stats_reading").html(data.online);
			$("#post_stats_writing").html(data.writers);
			$("#post_stats_total").html(ending(data.unique, "просмотр", "просмотра", "просмотров"));

		});
		x.subscribe("post_"+ id, "add_post_comment", function(data) {
			    var node = $(template("template_comment", data));
			    $("#placeholder_comment").append(node);
			    node.slideUp(0).slideDown(300);

			    if (waiting)
				    document.title = " ★ " + _title + " ★ ";

			    if ($("#new_comments_link").hasClass("g-disabled")) {
				    $("#new_comments_link").removeClass("g-disabled").click(function() {
					    location.hash = data.id;
				    });
			    }
			    commentPreview(node);
			    $.getJSON("http://"+ location.host +"/news/res/"+ id +"/");
		});
		x.subscribe("post_"+ id, "remove_post_comment", function(data) {
			$("#comment_" + data.id).slideUp(500);
		});
		x.subscribe("post_"+ id, "rate_post", function(data) {
			$("#post_"+ data.id +"_info .js-rate")
				.removeClass("g-red g-green").addClass((data.rate >= 0) ? "g-green" : "g-red")
				.html(data.rate);
		});
		x.subscribe("post_"+ id, "info_post", function(data) {
			var node = $("<div>", {"class": "b-comment"})
				.css({"background": "#FFB975", "color": "white", "padding": "5px 10px"})
				.html(data.comment);
			$("#placeholder_comment").append(node);
			node.show('blind', 800);

			if (waiting)
				document.title = " ★ " + _title + " ★ ";
		});

		commentPreview(document);
		x.addPageProcessor(":moderator", function(path, params) {
		    if (params)  {
			    $(".js-remove-button").removeClass("g-hidden").click(function() {
				    var comment_id = $("img", this).attr("alt");
				    $.getJSON("http://"+ location.host +"/mod/removePostComment/"+ comment_id +"/");
				    return false;
			    });
	        }
		});
	});


	x.addPageProcessor(/service\/last_board_posts\/?(\d+)?/, function(match) {
		var modMode = false, _title = document.title, waiting = false, page = match[1];
		var previewTimeout, previewActive, previewActiveLink, commentPreview = function(node, clone) {
			clone = clone || false;
			$(".js-cross-link", node).mouseover(function(e) {
				if (!$(this).data("preview_open"))
				{

					if (!clone && previewActiveLink) {
						$(".b-comment.m-tip").remove();
						$(previewActiveLink).data("preview_open", false);
						previewActiveLink = this;
					} else if (!previewActiveLink)
						previewActiveLink = this;

					previewActive     = true;
					previewTimeout    = clearTimeout(previewTimeout);

					var full_id = $(this).attr("name").split("/", 2);
					var id = full_id[1], board = full_id[0], el = $("#comment_"+ board +"_" + id);
					if ($(this).attr("name") != board +"/"+ id)
						return;

					if (el.length != 0) {
						var tip = $(el).clone()
						               .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
						               .addClass("m-tip")
							       	   .attr('id', '')
						               .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});
						$(document.body).append(tip);
						tip.slideUp(0).slideDown(300);
						$(this).data("preview_open", true);
						commentPreview(tip, true);
					} else {
						var link_ = this;
						$.getJSON("http://"+ location.host +"/"+ board +"/get/", {id: id}, function(data, status) {
							if (status != "error" && data != false) {
								var tip = $(template("template_comment", data))
									       .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
									       .addClass("m-tip")
									       .attr('id', '')
									       .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});

								$(document.body).append(tip);
								tip.slideUp(0).slideDown(300);
								$(link_).data("preview_open", true);
								commentPreview(tip, true);
							}
						});
					}
				}
				e.stopPropagation();
			});
		};
		$(document.body).mouseover(function(e) {
			if (!previewTimeout && previewActive) {
				previewTimeout = setTimeout(function() {
					$(".b-comment.m-tip").remove();
					$(previewActiveLink).data("preview_open", false);
					previewActiveLink = null;
					previewActive = false;
				}, 400);
			}
		});
		commentPreview(document);

		$(window)
			.bind("blur", function() {
				$(".b-comment.m-new").removeClass("m-new");
				waiting = true;
			})
			.bind("focus", function() {
				waiting = false;
				document.title = _title;
			});

		if (!page) {
			x.subscribe("board_global", "add_post_comment", function(data) {
					$.getJSON("http://"+ location.host +"/"+ data.board_id +"/get", {id: data.id}, function(data) {
						var node = $(template("template_comment", data));
						$("#placeholder_comment").prepend(node);
						node.slideUp(0).slideDown(300);
						commentPreview(node);

						if (waiting)
						    document.title = " ★ " + _title + " ★ ";

						if (modMode) {
							$(".js-remove-button", node).removeClass("g-hidden").click(function() {
								var full_id = $("img", this).attr("alt").split("/", 2);
								var board = full_id[0], comment_id = full_id[1];
								$.getJSON("http://"+ location.host +"/"+ board +"/remove/?id="+ comment_id);

								return false;
							});
						}
					});
			});

			x.subscribe("board_global", "remove_post_comment", function(data) {
				$("#comment_" + data.board_id +"_"+ data.id).slideUp(500);
			});
		}

		x.addPageProcessor(":moderator", function(path, params) {
			modMode = true;
			if (params) {
			    $(".js-remove-button").removeClass("g-hidden").click(function() {
				    var full_id = $("img", this).attr("alt").split("/", 2);
				    var board = full_id[0], comment_id = full_id[1];
				    $.getJSON("http://"+ location.host +"/"+ board +"/remove/?id="+ comment_id);

				    return false;
			    });
	        }
		});
	});


	x.addPageProcessor("fav", function(match) {
		var postloader = {}, updateMode = false, updateThread = null;
		var writing, waiting, writingTimeout = null;
		$(".js-update-post-button").removeClass("g-hidden");

		window['comment_callback'] = function(data) {
			$("#comment_form_error").html("");
			$("#comment_form [name=captcha]").val("");
			$("#comment_form input[type=submit]").attr("disabled", "");

			if (data["success"] == false) {
				var error_strings = [];
				for (var field in data["errors"])
					if (data["errors"].hasOwnProperty(field))
						error_strings.push(data["errors"][field]);

				if ("captcha" in data["errors"])
					$("#board_comment_captcha").dialog("open");

				$("#comment_form_error").html(error_strings.join(", "));
				return false;
			}
			$("#comment_form").get(0).reset();
		};

		$(".js-delete-button").click(function() {
			var password = prompt(board != "int" ? "Введите пароль удаления" : "Enter the password", "");

			if (password.length)
				$.getJSON($(this).attr("href") + "&password="+ encodeURIComponent(password));

			return false;
		});

		$(".js-favorite-button").click(function() {
			var img = $("img", this);
			$.getJSON(this.href, function(data, status) {
				if (data.favorite == true)
					$(img).attr("src", "http://"+ location.host +"/ico/favorites-true.png");
				else
					$(img).attr("src", "http://"+ location.host +"/ico/favorites-false.png");
			});
			return false;
		});

		var previewTimeout, previewActive, previewActiveLink, commentPreview = function(node, clone) {
			clone = clone || false;
			$(".js-cross-link", node).mouseover(function(e) {
				if (!$(this).data("preview_open"))
				{

					if (!clone && previewActiveLink) {
						$(".b-comment.m-tip").remove();
						$(previewActiveLink).data("preview_open", false);
						previewActiveLink = this;
					} else if (!previewActiveLink)
						previewActiveLink = this;

					previewActive     = true;
					previewTimeout    = clearTimeout(previewTimeout);

					var full_id = $(this).attr("name").split("/", 2);
					var id = full_id[1], board = full_id[0], el = $("#comment_"+ board +"_" + id);

					if ($(this).attr("name") != board +"/"+ id)
						return;

					if (el.length != 0) {
						var tip = $(el).clone()
						               .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
						               .addClass("m-tip")
							       	   .attr('id', '')
						               .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});
						$(document.body).append(tip);
						tip.slideUp(0).slideDown(300);
						$(this).data("preview_open", true);
						commentPreview(tip, true);
					} else {
						var link_ = this;
						$.getJSON("http://"+ location.host +"/"+ board +"/get/", {id: id}, function(data, status) {
							if (status != "error" && data != false) {
								var tip = $(template("template_comment", data))
									       .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
									       .addClass("m-tip")
									       .attr('id', '')
									       .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});

								$(document.body).append(tip);
								tip.slideUp(0).slideDown(300);
								$(link_).data("preview_open", true);
								commentPreview(tip, true);
							}
						});
					}
				}
				e.stopPropagation();
			});
		};
		$(document.body).mouseover(function(e) {
			if (!previewTimeout && previewActive) {
				previewTimeout = setTimeout(function() {
					$(".b-comment.m-tip").remove();
					$(previewActiveLink).data("preview_open", false);
					previewActiveLink = null;
					previewActive = false;
				}, 400);
			}
		});
		commentPreview(document);

		$(".js-postload-link").click(function() {
			var full_id = $(this).attr("name").split("/", 2);
			var id = full_id[1], board = full_id[0];

			if (postloader[board +"_"+ id] && postloader[board +"_"+ id].length)
			{
				$.getJSON("http://"+ location.host +"/"+ board +"/get", {id: postloader[board +"_"+ id]}, function(data) {
					for(var i in data) {
						var node = $(template("template_comment", data[i]));
				    		$("#placeholder_comment_"+ board +"_"+ id).append(node);
				    		node.slideUp(0).slideDown(300);
				    		commentPreview(node);
					}
					postloader[board +"_"+ id] = [];
					$("#board_"+ board +"_"+ id +"_postload").hide();
					$("#post_"+ board +"_"+ id +"_info .js-comments").removeClass("g-bold")
				});
			}
		});

		$(".js-thread-load").click(function() {
			var full_id = $(this).attr("name").split("/", 2);
			var nm = full_id[1], board = full_id[0];

			$.getJSON("http://"+ location.host +"/"+ board +"/get", {thread_id: nm}, function(data) {
				$("#placeholder_comment_"+ board +"_"+ nm).html("");				
				for(var i in data) {
					var node = $(template("template_comment", data[i]));
				    	$("#placeholder_comment_"+ board +"_"+ nm).append(node);
				    	commentPreview(node);
				}
				postloader[nm] = [];
				$("#board_"+ board +"_"+ nm +"_postload").hide();
				$("#board_"+ board +"_"+ nm +"_thread_load").hide();
				$("#post_"+ nm +"_info .js-comments").removeClass("g-bold");
			});
		});

		x.subscribe("board_global", "add_post_comment", function(data) {
				if (!$("#post_"+ data.board_id +"_"+ data.parent_id).length)
					return false;

				if (updateMode && updateThread == data.board_id +"_"+ data.parent_id) {
					$.getJSON("http://"+ location.host +"/"+ data.board_id +"/get", {id: data.id}, function(data) {
						var node = $(template("template_comment", data));
						$("#placeholder_comment_"+ data.board_id +"_"+ data.parent_id).append(node);
						node.slideUp(0).slideDown(300);
						commentPreview(node);
					});
					$("#post_"+ data.board_id +"_"+ data.parent_id +"_info .js-comments").html(data.count);
					return false;
				}

				postloader[data.board_id +"_"+ data.parent_id] ?
					postloader[data.board_id +"_"+ data.parent_id].push(data.id) :
					postloader[data.board_id +"_"+ data.parent_id] = [data.id];

				$("#board_"+ data.board_id +"_"+ data.parent_id +"_postload")
					.show().find(".js-postload-num").html(
						ending(postloader[data.board_id +"_"+ data.parent_id].length, "новый ответ", "новых ответа", "новых ответов")
					);
	
				$("#post_"+ data.board_id +"_"+ data.parent_id +"_info .js-comments").addClass("g-bold").html(data.count);
		});


		$(".js-post-id-link").click(function() {
			if (updateMode) {
				insertText(">>" + id);
				return false;
			}
		});

		$(".js-paste-link").live("click", function() {
			if (updateMode) {
				insertText(">>" + $(this).attr("name"));
				return false;
			}
		});
		
		var backtile = $("<div>").css({"background": "rgba(255, 255, 255, .5)", "width": "100%", "height": "100%", "position":"absolute", "top": 0, "left": 0, "zIndex": 100}).appendTo("body").hide().click(function() { $(this).hide("fade", function() { $(this).css("position", "absolute"); }); }),
			imgwrap = $("<div>").css({"position": "absolute", "top": "50%", "left": "50%"}).appendTo(backtile),
			img = $("<img>").attr("alt", "Кликните мышкой, чтобы скрыть просматриваемое изображение.").css({"background": "#323232", "border": "5px solid #fff", "box-shadow": "2px 2px 5px rgba(0, 0, 0, .3)", "border-radius": "8px", "margin-top": "-50%", "margin-left": "-50%", "position": "relative"}).appendTo(imgwrap);

		$(".b-image-link").live("click", function() { 
		   var size = /(\d+)x(\d+)/.exec($(this).attr("title")),
			   x = size[1], y = size[2], 
			   max_x = document.documentElement.clientWidth,
			   max_y = document.documentElement.clientHeight;

		   if (x >= max_x - 100 || y >= max_y - 50) return true;
	
		   img
			  .attr("src", $(this).attr("href"))
			  .css({"width": x, "height": y, "margin-top": -y/2, "margin-left": -x/2});
		   
		   backtile.css("position", "fixed").show("fade", 500);
		   
		   return false;
		});

		$("#board_comment_captcha").dialog({
	            autoOpen: false,
		    modal: true,
	            resizable: false,
	            width: 250,
		    open: function() {
			var img = $("img", this).get(0);
			img.src = img.src.replace(/rand=(\d+)?$/, "rand="+ Math.random());

		        $("input", this).focus().val("");
		    },
		    buttons: {
			    "Отмена": function() {
				    $(this).dialog("close");
		            },
			    "Отправить": function() {
				    $("#comment_form input[name=captcha]").val($("input", this).val());
				    $("#comment_form").submit();
				    $(this).dialog("close");
		            }
		    },
	            show: 'fade', hide: 'fade'
		});

		$("#board_comment_captcha").keyup(function(e){
			if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
				$(".ui-dialog:visible").find('.ui-dialog-buttonpane').find('button:last').trigger("click");
				return false;
			}
             	});

		$(".js-update-post-button").click(function(e) {
			e.stopPropagation();
			if (updateMode == true) {
				$("#comment_form").remove();
				if (updateThread == $(this).attr("name").replace("/", "_")) {
					updateMode = false;
					updateThread = null;
					return;
				}		
			}

			var full_id = $(this).attr("name").split("/", 2);
			var id = full_id[1], board = full_id[0];

			updateMode = true;
			updateThread = board +"_"+ id;

			if (postloader[updateThread] && postloader[updateThread].length)
			{
				$.getJSON("http://"+ location.host +"/"+ board +"/get", {id: postloader[updateThread]}, function(data) {
					for(var i in data) {
						var node = $(template("template_comment", data[i]));
					    	$("#placeholder_comment_"+ updateThread).append(node);
					    	node.slideUp(0).slideDown(300);
					    	commentPreview(node);
					}
					postloader[updateThread] = [];
					$("#board_"+ updateThread +"_postload").hide();
					$("#post_"+ updateThread +"_info .js-comments").removeClass("g-bold")
				});
			}

			$("#placeholder_form_comment_"+ updateThread)
				.html(template("template_form_comment", {"board": board, "id": id, "textarea": "</textarea>"}));
			
			$("#comment_form")
				.attr("target", "board_form_iframe")
				.attr("action", "http://"+ location.host +"/"+ board +"/createPostAjaxForm/")
				.submit(function() {
					$("#comment_form input[type=submit]").attr("disabled", "disabled");
				});

			var statusReading = function(id) {
				$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ id +"/stats/?writing=0");
				writing = false;
			};

			$("#comment_form_text")
				.bind("keyup", function(e) {
					if (!writing) {
						$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ id +"/stats/?writing=1");
						writing = true;
					}

					if (writingTimeout) clearTimeout(writingTimeout);
					writingTimeout = setTimeout(statusReading, 5000);
					e.stopPropagation();
				})
				.bind("blur", function() {
					$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ id +"/stats/?writing=0");
					writing = false;
				})
				.bind("keyup", function(e) {
					if ((e.ctrlKey || e.metaKey) && (e.keyCode==10 || e.keyCode==13)) {
						$("#comment_form").submit();
					}
				});


			$("#comment_form .js-homeboard-link").click(function() {
				$("#comment_form .js-homeboard-select").toggle();
			});

			$("#comment_form .js-homeboard-select").bind("mouseup", function(e) {
				e.stopPropagation();
			});
		
			$("#comment_form .js-homeboard-select-link").click(function(e) {
				var board = $(this).attr("name");
				$("input[name=homeboard]").val(board);
				$.cookie("homeboard", board, {expires: 356, path: "/"});

				$(".js-homeboard-icon").attr("src", $("img", this).attr("src"));
				$(".js-homeboard-select").hide();
			});
		});
	});

	var post_filters = [];
	x.addPageProcessor(/^(d|b|a|vg|s|pr|mu|tv|to|wi|int|a.{3}e)\/?(\d+)?$/, function(match) {
		var board = match[1], postloader = {}, updateMode = false, updateThread = null;
		var writing, waiting, writingTimeout = null, _title = document.title;
		$(".js-update-post-button").removeClass("g-hidden");

		window['board_callback'] = function(data) {
			$("#board_form_error").html("");
			$("#board_form input, #board_form textarea").removeClass("g-input-error");
			$("#board_form input[type=submit]").attr("disabled", "");

			if (data["success"] == false)
			{
				var error_strings = [];
				for (var field in data["errors"])
				if (data["errors"].hasOwnProperty(field))
				{
					error_strings.push(data["errors"][field]);
					$("#board_form [name="+ field +"]").addClass("g-input-error");
				}

				$("#board_form [name=captcha]").val("");
				$("#board_form_error").html(error_strings.join(", "));
				return false;
			}

			$("#board_form").get(0).reset();
			location.href = "http://"+ location.host +"/"+ board +"/res/"+ data.id +"/";
			throw void(0);
		};

		window['comment_callback'] = function(data) {
			$("#comment_form_error").html("");
			$("#comment_form [name=captcha]").val("");
			$("#comment_form input[type=submit]").attr("disabled", "");

			if (data["success"] == false) {
				var error_strings = [];
				for (var field in data["errors"])
					if (data["errors"].hasOwnProperty(field))
						error_strings.push(data["errors"][field]);

				if ("captcha" in data["errors"])
					$("#board_comment_captcha").dialog("open");

				$("#comment_form_error").html(error_strings.join(", "));
				return false;
			}
			$("#comment_form").get(0).reset();
		};

		$(window)
			.bind("blur", function() {
				$(".b-comment.m-new").removeClass("m-new");
				waiting = true;
			})
			.bind("focus", function() {
				waiting = false;
				document.title = _title;
			});

		$(".js-delete-button").click(function() {
			var password = prompt(board != "int" ? "Введите пароль удаления" : "Enter the password", "");

			if (password.length)
				$.getJSON($(this).attr("href") + "&password="+ encodeURIComponent(password));

			return false;
		});

		$(".js-favorite-button").click(function() {
			var img = $("img", this);
			$.getJSON(this.href, function(data, status) {
				if (data.favorite == true)
					$(img).attr("src", "http://"+ location.host +"/ico/favorites-true.png");
				else
					$(img).attr("src", "http://"+ location.host +"/ico/favorites-false.png");
			});
			return false;
		});

		$(".js-subscribe-checkbox").bind("change", function() {
			if ($(this).is(":checked")) {
				$.getJSON("http://"+ location.host +"/service/subscribeBoard/"+ board +"/");
				console.log("subscibed");
			} else {
 				$.getJSON("http://"+ location.host +"/service/unsubscribeBoard/"+ board +"/"); 
			}
		});

		var previewTimeout, previewActive, previewActiveLink, commentPreview = function(node, clone) {
			clone = clone || false;
			$(".js-cross-link", node).mouseover(function(e) {
				if (!$(this).data("preview_open"))
				{

					if (!clone && previewActiveLink) {
						$(".b-comment.m-tip").remove();
						$(previewActiveLink).data("preview_open", false);
						previewActiveLink = this;
					} else if (!previewActiveLink)
						previewActiveLink = this;

					previewActive     = true;
					previewTimeout    = clearTimeout(previewTimeout);

					var id = $(this).text().replace(/\D/g, ""), el = $("#comment_"+ board +"_" + id);

					if ($(this).attr("name") != board +"/"+ id)
						return;

					if (el.length != 0) {
						var tip = $(el).clone()
						               .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
						               .addClass("m-tip")
							       	   .attr('id', '')
						               .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});
						$(document.body).append(tip);
						tip.slideUp(0).slideDown(300);
						$(this).data("preview_open", true);
						commentPreview(tip, true);
					} else {
						var link_ = this;
						$.getJSON("http://"+ location.host +"/"+ board +"/get/", {id: id}, function(data, status) {
							if (status != "error" && data != false) {
								var tip = $(template("template_comment", data))
									       .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
									       .addClass("m-tip")
									       .attr('id', '')
									       .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});

								$(document.body).append(tip);
								tip.slideUp(0).slideDown(300);
								$(link_).data("preview_open", true);
								commentPreview(tip, true);
							}
						});
					}
				}
				e.stopPropagation();
			});
		};
		$(document.body).mouseover(function(e) {
			if (!previewTimeout && previewActive) {
				previewTimeout = setTimeout(function() {
					$(".b-comment.m-tip").remove();
					$(previewActiveLink).data("preview_open", false);
					previewActiveLink = null;
					previewActive = false;
				}, 400);
			}
		});
		commentPreview(document);

		$(".js-postload-link").click(function() {
			var nm = $(this).attr("name");

			if (postloader[nm] && postloader[nm].length)
			{
				$.getJSON("http://"+ location.host +"/"+ board +"/get", {id: postloader[nm]}, function(data) {
					for(var i in data) {
						var node = $(template("template_comment", data[i]));
				    	$("#placeholder_comment_"+ board +"_"+ nm).append(node);
				    	node.slideUp(0).slideDown(300);
				    	commentPreview(node);
					}
					postloader[nm] = [];
					$("#board_"+ board +"_"+ nm +"_postload").hide();
					$("#post_"+ nm +"_info .js-comments").removeClass("g-bold")
				});
			}
		});

		$(".js-thread-load").click(function() {
			var nm = $(this).attr("name");

			$.getJSON("http://"+ location.host +"/"+ board +"/get", {thread_id: nm}, function(data) {
				$("#placeholder_comment_"+ board +"_"+ nm).html("");				
				for(var i in data) {
					var node = $(template("template_comment", data[i]));
				    	$("#placeholder_comment_"+ board +"_"+ nm).append(node);
				    	commentPreview(node);
				}
				postloader[nm] = [];
				$("#board_"+ board +"_"+ nm +"_postload").hide();
				$("#board_"+ board +"_"+ nm +"_thread_load").hide();
				$("#post_"+ nm +"_info .js-comments").removeClass("g-bold");
			});
		});
		
		
		
		var backtile = $("<div>").css({"background": "rgba(255, 255, 255, .5)", "width": "100%", "height": "100%", "position":"absolute", "top": 0, "left": 0, "zIndex": 100}).appendTo("body").hide().click(function() { $(this).hide("fade", function() { $(this).css("position", "absolute"); }); }),
			imgwrap = $("<div>").css({"position": "absolute", "top": "50%", "left": "50%"}).appendTo(backtile),
			img = $("<img>").attr("alt", "Кликните мышкой, чтобы скрыть просматриваемое изображение.").css({"background": "#323232", "border": "5px solid #fff", "box-shadow": "2px 2px 5px rgba(0, 0, 0, .3)", "border-radius": "8px", "margin-top": "-50%", "margin-left": "-50%", "position": "relative"}).appendTo(imgwrap);

		$(".b-image-link").live("click", function() { 
		   var size = /(\d+)x(\d+)/.exec($(this).attr("title")),
			   x = size[1], y = size[2], 
			   max_x = document.documentElement.clientWidth,
			   max_y = document.documentElement.clientHeight;

		   if (x >= max_x - 100 || y >= max_y - 50) return true;
	
		   img
			  .attr("src", $(this).attr("href"))
			  .css({"width": x, "height": y, "margin-top": -y/2, "margin-left": -x/2});
		   
		   backtile.css("position", "fixed").show("fade", 500);
		   
		   return false;
		});

		x.subscribe("board_"+ board, "add_post", function(data) {
			$("#post_notify").show("fade");
		});

		x.subscribe("board_"+ board, "add_post_comment", function(data) {
				if (!$("#post_"+ data.board_id +"_"+ data.parent_id).length)
					return false;

				$.getJSON("/service/notifyCheck/"+ data.board_id +"/"+ data.parent_id);
				post_filters.push(data.id);
			    	if (waiting)
				    document.title = " ★ " + _title + " ★ ";

				if (updateMode && data.parent_id == updateThread) {
					$.getJSON("http://"+ location.host +"/"+ board +"/get", {id: data.id}, function(data) {
						var node = $(template("template_comment", data));
						$("#placeholder_comment_"+ board +"_"+ data.parent_id).append(node);
						node.slideUp(0).slideDown(300);
						commentPreview(node);
					});
					$("#post_"+ data.parent_id +"_info .js-comments").html(data.count);
					return false;
				}
				postloader[data.parent_id] ?
					postloader[data.parent_id].push(data.id) :
					postloader[data.parent_id] = [data.id];

                if (board != "int")
				    $("#board_"+ board +"_"+ data.parent_id +"_postload")
					    .show().find(".js-postload-num").html(
						    ending(postloader[data.parent_id].length, "новый ответ", "новых ответа", "новых ответов")
					    );
		        else
				    $("#board_"+ board +"_"+ data.parent_id +"_postload")
					    .show().find(".js-postload-num").html(postloader[data.parent_id].length);
	
				$("#post_"+ data.parent_id +"_info .js-comments").addClass("g-bold").html(data.count);
		});

		$("#board_captcha").dialog({
	            autoOpen: false,
		    modal: true,
	            resizable: false,
	            width: 250,
		    open: function() {
			var img = $("img", this).get(0);
			img.src = img.src.replace(/rand=(\d+)?$/, "rand="+ Math.random());

		        $("input", this).focus().val("");
		    },
		    buttons: {
			    "Cancel": function() {
				    $(this).dialog("close");
		            },
			    "OK": function() {
				    $("input[name=captcha]").val($("input", this).val());
				    $("#board_form").submit();
				    $(this).dialog("close");
		            }
		    },
	            show: 'fade', hide: 'fade'
		});

		$("#board_captcha, #board_comment_captcha").keyup(function(e){
			if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
				$(".ui-dialog:visible").find('.ui-dialog-buttonpane').find('button:last').trigger("click");
				return false;
			}
             	});

		$("#board_form")
			.attr("target", "board_form_iframe")
			.attr("action", "http://"+ location.host +"/"+ board +"/createAjaxForm/")
			.submit(function() {
				if ($("input[name=captcha]").val().length == 0) {
					$("#board_captcha").dialog("open");
					return false;
				}
				$("#board_form input[type=submit]").attr("disabled", "disabled");
			});

		$(".js-post-id-link").click(function() {
			if (updateMode) {
				insertText(">>" + id);
				return false;
			}
		});

		$(".js-paste-link").live("click", function() {
			if (updateMode) {
				insertText(">>" + $(this).attr("name"));
				return false;
			}
		});

		$("#board_comment_captcha").dialog({
	            autoOpen: false,
		    modal: true,
	            resizable: false,
	            width: 250,
		    open: function() {
			var img = $("img", this).get(0);
			img.src = img.src.replace(/rand=(\d+)?$/, "rand="+ Math.random());

		        $("input", this).focus().val("");
		    },
		    buttons: {
			    "Cancel": function() {
				    $(this).dialog("close");
		            },
			    "OK": function() {
				    $("#comment_form input[name=captcha]").val($("input", this).val());
				    $("#comment_form").submit();
				    $(this).dialog("close");
		            }
		    },
	            show: 'fade', hide: 'fade'
		});

		$(".js-update-post-button").click(function(e) {
			e.stopPropagation();
			if (updateMode == true) {
				$("#comment_form").remove();
				if (updateThread == $(this).attr("name")) {
					updateMode = false;
					updateThread = null;
					return;
				}		
			}

			updateMode = true;
			updateThread = $(this).attr("name");

			if (postloader[updateThread] && postloader[updateThread].length)
			{
				$.getJSON("http://"+ location.host +"/"+ board +"/get", {id: postloader[updateThread]}, function(data) {
					for(var i in data) {
						var node = $(template("template_comment", data[i]));
					    	$("#placeholder_comment_"+ board +"_"+ updateThread).append(node);
					    	node.slideUp(0).slideDown(300);
					    	commentPreview(node);
					}
					postloader[updateThread] = [];
					$("#board_"+ board +"_"+ updateThread +"_postload").hide();
					$("#post_"+ updateThread +"_info .js-comments").removeClass("g-bold")
				});
			}

			$("#placeholder_form_comment_"+ board +"_"+ updateThread)
				.html(template("template_form_comment", {"id": updateThread, "textarea": "</textarea>"}));
			
			$("#comment_form")
				.attr("target", "board_form_iframe")
				.attr("action", "http://"+ location.host +"/"+ board +"/createPostAjaxForm/")
				.submit(function() {
					$("#comment_form input[type=submit]").attr("disabled", "disabled");
				});

			var statusReading = function(id) {
				$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ updateThread +"/stats/?writing=0");
				writing = false;
			};

			$("#comment_form_text")
				.bind("keyup", function(e) {
					if (!writing) {
						$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ updateThread +"/stats/?writing=1");
						writing = true;
					}

					if (writingTimeout) clearTimeout(writingTimeout);
					writingTimeout = setTimeout(statusReading, 5000);
					e.stopPropagation();
				})
				.bind("blur", function() {
					$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ updateThread +"/stats/?writing=0");
					writing = false;
				})
				.bind("keyup", function(e) {
					if ((e.ctrlKey || e.metaKey) && (e.keyCode==10 || e.keyCode==13)) {
						$("#comment_form").submit();
					}
				});

			$("#comment_form .js-homeboard-link").click(function() {
				$("#comment_form .js-homeboard-select").toggle();
			});

			$("#comment_form .js-homeboard-select").bind("mouseup", function(e) {
				e.stopPropagation();
			});
		
			$("#comment_form .js-homeboard-select-link").click(function(e) {
				var board = $(this).attr("name");
				$("input[name=homeboard]").val(board);
				$.cookie("homeboard", board, {expires: 356, path: "/"});

				$(".js-homeboard-icon").attr("src", $("img", this).attr("src"));
				$(".js-homeboard-select").hide();
			});
		
			(function() {
				var board = $.cookie("homeboard");
				if (board) {
					var el = $("#comment_form .js-homeboard-select a[name="+ board +"]");
					if (el.length) {
						$("input[name=homeboard]").val(board);
						$(".js-homeboard-icon").attr("src", $("img", el).attr("src"));
					}
				}
			})();
		});

		$("#board_form .js-homeboard-link").click(function() {
			$("#board_form .js-homeboard-select").toggle();
		});

		$(document.body).bind("mouseup", function() {
			$(".js-homeboard-select").hide();
		});

		$("#board_form .js-homeboard-select").bind("mouseup", function(e) {
			e.stopPropagation();
		});
		
		$("#board_form .js-homeboard-select-link").click(function(e) {
			var board = $(this).attr("name");
			$("input[name=homeboard]").val(board);
			$.cookie("homeboard", board, {expires: 356, path: "/"});

			$(".js-homeboard-icon").attr("src", $("img", this).attr("src"));
			$(".js-homeboard-select").hide();
		});

		x.addPageProcessor(":moderator", function(path, params) {
		    if (params) {
			    var link = $('<a href="javascript://"><sup>Изменить название</sup></a>');
			    link.click(function() {
				    var title       = prompt("title", $(".b-board-header_name h1").text());
				    var description = prompt("description", $(".b-board-header_desc").text());
				    $.getJSON("http://"+ location.host +"/"+ board +"/changeTitle", {title:title, description:description}); 
			    });
			    $(".b-board-header_options").append(link);
	        }
		});
	});

	x.addPageProcessor(/^(d|b|a|vg|s|pr|mu|tv|to|wi|int|a.{3}e)\/res\/(\d+)/, function(match) {
		var board = match[1], id = match[2];

		window['comment_callback'] = function(data) {
			$("#comment_form_error").html("");
			$("#comment_form [name=captcha]").val("");
			$("#comment_form input[type=submit]").attr("disabled", "");

			if (data["success"] == false) {
				var error_strings = [];
				for (var field in data["errors"])
					if (data["errors"].hasOwnProperty(field))
						error_strings.push(data["errors"][field]);

				if ("captcha" in data["errors"])
					$("#board_comment_captcha").dialog("open");

				$("#comment_form_error").html(error_strings.join(", "));
				return false;
			}
			$("#comment_form").get(0).reset();
		};

		var writing, waiting, writingTimeout = null, _title = document.title,
		   statusReading = function() {
			$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ id +"/stats/?writing=0");
			writing = false;
		};
		var scrollingInterval = null,
		    scrollEnable = function() {
			if (scrollingInterval) clearInterval(scrollingInterval);
			var cacheOffsetHeight = parseInt(document.body.offsetHeight);
			scrollingInterval = setInterval(function() {
				if (parseInt(document.body.offsetHeight) != cacheOffsetHeight)
					window.scrollBy(0, parseInt(document.body.offsetHeight) - cacheOffsetHeight);

				cacheOffsetHeight = parseInt(document.body.offsetHeight);
			}, 15);
		    },
		    scrollDisable = function() {
			if (scrollingInterval) clearInterval(scrollingInterval);
		    };
		var hash = "";
		setInterval(function() {
			if (window.location.hash != hash) {
				hash = window.location.hash;
				$(".b-comment").removeClass("m-selected");
				$("#comment_"+hash.substring(1)).addClass("m-selected");
			}
		}, 100);
		var previewTimeout, previewActive, previewActiveLink, commentPreview = function(node, clone) {
			clone = clone || false;
			$(".js-cross-link", node).mouseover(function(e) {
				if (!$(this).data("preview_open"))
				{

					if (!clone && previewActiveLink) {
						$(".b-comment.m-tip").remove();
						$(previewActiveLink).data("preview_open", false);
						previewActiveLink = this;
					} else if (!previewActiveLink)
						previewActiveLink = this;

					previewActive     = true;
					previewTimeout    = clearTimeout(previewTimeout);

					var id = $(this).text().replace(/\D/g, ""), el = $("#comment_"+ board +"_" + id);

					if ($(this).attr("name") != board +"/"+ id)
						return;

					if (el.length != 0) {
						var tip = $(el).clone()
						               .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
						               .addClass("m-tip")
							       	   .attr('id', '')
						               .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});
						$(document.body).append(tip);
						tip.slideUp(0).slideDown(300);
						$(this).data("preview_open", true);
						commentPreview(tip, true);
					} else {
						var link_ = this;
						$.getJSON("http://"+ location.host +"/"+ board +"/get/", {id: id}, function(data, status) {
							if (status != "error" && data != false) {
								var tip = $(template("template_comment", data))
									       .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
									       .addClass("m-tip")
									       .attr('id', '')
									       .css({display:'block', width: '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});

								$(document.body).append(tip);
								tip.slideUp(0).slideDown(300);
								$(link_).data("preview_open", true);
								commentPreview(tip, true);
							}
						});
					}
				}
				e.stopPropagation();
			});
		};
		$(document.body).mouseover(function(e) {
			if (!previewTimeout && previewActive) {
				previewTimeout = setTimeout(function() {
					$(".b-comment.m-tip").remove();
					$(previewActiveLink).data("preview_open", false);
					previewActiveLink = null;
					previewActive = false;
				}, 400);
			}
		});

		var contentBottom = function() {
			var el = $(".b-blog-entry"), offset = el.offset(), outHeight = el.outerHeight(true);
			return offset.top + outHeight;
		}();

		$(".b-comment-form_b-uplink a").click(function() {
			$(window).scrollTo(0, 0);
		});

		$(".js-post-id-link").click(function() {
			insertText(">>" + id);
			return false;
		});

		$(".js-paste-link").live("click", function() {
			insertText(">>" + $(this).attr("name"));
			return false;
		});

		$(".js-favorite-button").click(function() {
			var img = $("img", this);
			$.getJSON(this.href, function(data, status) {
				if (data.favorite == true)
					$(img).attr("src", "http://"+ location.host +"/ico/favorites-true.png");
				else
					$(img).attr("src", "http://"+ location.host +"/ico/favorites-false.png");
			});
			return false;
		});

		$(".js-delete-button").click(function() {
			var password = prompt(board != "int" ? "Введите пароль удаления" : "Enter the password", "");

			if (password.length)
				$.getJSON($(this).attr("href") + "&password="+ encodeURIComponent(password));

			return false;
		});
		
		$("#board_comment_captcha").dialog({
	            autoOpen: false,
		    modal: true,
	            resizable: false,
	            width: 250,
		    open: function() {
			var img = $("img", this).get(0);
			img.src = img.src.replace(/rand=(\d+)?$/, "rand="+ Math.random());

		        $("input", this).focus().val("");
		    },
		    buttons: {
			    "Cancel": function() {
				    $(this).dialog("close");
		            },
			    "OK": function() {
				    $("#comment_form input[name=captcha]").val($("input", this).val());
				    $("#comment_form").submit();
				    $(this).dialog("close");
		            }
		    },
	            show: 'fade', hide: 'fade'
		});

		$("#board_comment_captcha").keyup(function(e){
			if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
				$(".ui-dialog:visible").find('.ui-dialog-buttonpane').find('button:last').trigger("click");
				return false;
			}
             	});

		$("#comment_form")
			.attr("target", "comment_form_iframe")
			.attr("action", "http://"+ location.host +"/"+ board +"/createPostAjaxForm/")
			.submit(function() {
				$("#comment_form input[type=submit]").attr("disabled", "disabled");
			});

		$("#comment_form_text")
			.bind("keyup", function(e) {
				if (!writing) {
					$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ id +"/stats/?writing=1");
					writing = true;
				}

				if (writingTimeout) clearTimeout(writingTimeout);
				writingTimeout = setTimeout(statusReading, 5000);
				e.stopPropagation();
			})
			.bind("focus", function() {
				scrollEnable();
			})
			.bind("blur", function() {
				$.getJSON("http://"+ location.host +"/"+ board +"/res/"+ id +"/stats/?writing=0");
				writing = false;
				scrollDisable();
			})
			.bind("keyup", function(e) {
				if ((e.ctrlKey || e.metaKey) && (e.keyCode==10 || e.keyCode==13)) {
						$("#comment_form").submit();
				}
			});
		
		$(".js-homeboard-link").click(function() {
			$(".js-homeboard-select").toggle();
		});

		$(document.body).bind("mouseup", function() {
			$(".js-homeboard-select").hide();
		});

		$(".js-homeboard-select").bind("mouseup", function(e) {
			e.stopPropagation();
		});
		
		$(".js-homeboard-select-link").click(function(e) {
			var board = $(this).attr("name");
			$("input[name=homeboard]").val(board);
			$.cookie("homeboard", board, {expires: 356, path: "/"});

			$(".js-homeboard-icon").attr("src", $("img", this).attr("src"));
			$(".js-homeboard-select").hide();
		});

		$(window)
			.bind("blur", function() {
				$(".b-comment.m-new").removeClass("m-new");
				waiting = true;
			})
			.bind("focus", function() {
				waiting = false;
				document.title = _title;
			});
		
		
		
		var backtile = $("<div>").css({"background": "rgba(255, 255, 255, .5)", "width": "100%", "height": "100%", "position":"absolute", "top": 0, "left": 0, "zIndex": 100}).appendTo("body").hide().click(function() { $(this).hide("fade", function() { $(this).css("position", "absolute"); }); }),
			imgwrap = $("<div>").css({"position": "absolute", "top": "50%", "left": "50%"}).appendTo(backtile),
			img = $("<img>").attr("alt", "Кликните мышкой, чтобы скрыть просматриваемое изображение.").css({"background": "#323232", "border": "5px solid #fff", "box-shadow": "2px 2px 5px rgba(0, 0, 0, .3)", "border-radius": "8px", "margin-top": "-50%", "margin-left": "-50%", "position": "relative"}).appendTo(imgwrap);

		$(".b-image-link").live("click", function() { 
		   var size = /(\d+)x(\d+)/.exec($(this).attr("title")),
			   x = size[1], y = size[2], 
			   max_x = document.documentElement.clientWidth,
			   max_y = document.documentElement.clientHeight;

		   if (x >= max_x - 100 || y >= max_y - 50) return true;
	
		   img
			  .attr("src", $(this).attr("href"))
			  .css({"width": x, "height": y, "margin-top": -y/2, "margin-left": -x/2});
		   
		   backtile.css("position", "fixed").show("fade", 500);
		   
		   return false;
		});

		x.subscribe("boardpost_"+ board +"_"+ id, "stats_updated", function(data) {
			$("#post_stats_reading").html(data.online);
			$("#post_stats_writing").html(data.writers);

		});
		x.subscribe("boardpost_"+ board +"_"+ id, "remove_post", function(data) {
			location.reload();
		});
		x.subscribe("boardpost_"+ board +"_"+ id, "add_post_comment", function(data) {
			    $.getJSON("/service/notifyCheck/"+ data.board_id +"/"+ data.parent_id);
		            post_filters.push(data.id);

			    var node = $(template("template_comment", data));
			    $("#placeholder_comment").append(node);
			    node.slideUp(0).slideDown(300);

			    if (waiting)
				    document.title = " ★ " + _title + " ★ ";

			    if ($("#new_comments_link").hasClass("g-disabled")) {
				    $("#new_comments_link").removeClass("g-disabled").click(function() {
					    location.hash = data.id;
				    });
			    }
			    commentPreview(node);
		});
		x.subscribe("boardpost_"+ board +"_"+ id, "remove_post_comment", function(data) {
			$("#comment_" + board +"_"+ data.id).slideUp(500);
		});

		commentPreview(document);
		x.addPageProcessor(":moderator", function(path, params) {
		    if (params) {
			    $(".js-remove-button")
                    .removeClass("g-hidden").click(function() {
				        var comment_id = $("img", this).attr("alt"),
                            delall     = confirm('Удалить все ответы от автора?');
				        $.getJSON("http://"+ location.host +"/"+ board +"/remove/?id="+ comment_id + (delall ? "&delall=1" : ""));
				        return false;
			        });
	        }
		});
	});

	x.addPageProcessor("live", function() {
		var resetFileds = function(reset) {
			var link        = $("#add_link_form input[name=link]").val(),
			    description = $("#add_link_form input[name=description]").val();

			if (reset || (link == "" && description == "") || (link == "Ссылка" && description == "Краткое описание")) {
				$("#add_link_form input[name=link]").val("Ссылка").css({color: "#ddd"}).one("focus", function() {
					$(this).val("").css({color: "#222"});
				});
				$("#add_link_form input[name=description]").val("Краткое описание").css({color: "#ddd"}).one("focus", function() {
					$(this).val("").css({color: "#222"});
				});
			}
		},
		   reloadPage = function() {
			$.getJSON("http://"+ location.host +"/live/", function(data, status) {
				if (status != "error") {
					if (data.length == 0) {
						$("#no_entries").removeClass("g-hidden");
						$("#placeholder_link .b-live-entry").remove();
					} else {
						$("#no_entries").addClass("g-hidden");
						$("#placeholder_link .b-live-entry").remove();
						for(var i in data) {
							var node = $(template("template_link", data[i]));
							$("#placeholder_link").append(node);
						}
					}
				}
			});
		}, filter = true;

		$.getJSON("http://"+ location.host +"/live/set_filter/", function(data) {filter = data});

		$("#filter_link_form").submit(function() {
			$.getJSON("http://"+ location.host +"/live/set_filter/?"+ $("#filter_link_form").serialize(), function(data) {
				reloadPage();
				filter = data;
			});
			return false;
		});

		$("#add_link_form input[type=submit]").click(function() {
			$("#live_form_error").html("");
			$.post(
				"http://"+ location.host +"/live/add/",
				{
					"link": $("#add_link_form input[name=link]").val(),
					"description": $("#add_link_form input[name=description]").val()
				},
				function(result, status) {
					if (status != "error") {
						if (result === true) {
							$("#live_form_error").val("");
							resetFileds(true);
						}

						if (result.isValid == "captcha") {
							$("#add_link_form").submit();
						}

						if (result.isValid != true)
						{
							var error_strings = [];
							for (var field in result.validationResults)
							if (result.validationResults.hasOwnProperty(field))
								error_strings.push(result.validationResults[field]);
							$("#live_form_error").html(error_strings.join(", "));
							return false;
						}
					}
				},
				"json"
			);
			return false;
		});

		$(".js-open-right-panel").click(function() {
            $.getJSON("http://"+ location.host +"/live/linksPanel/", {status: "on"}, function(data, status) {
                location.reload();
            });
            return false;
        });

		x.subscribe("live", "add_online_link", function(data) {
			if (filter instanceof Array && filter.indexOf(data.board) == -1)
				return;

			$("#no_entries").addClass("g-hidden");

			var node = $(template("template_link", data));
			$("#placeholder_link").prepend(node);

			node.slideUp(0).slideDown(500);

			$(".b-live-entry_b-description", node).click(function(e) {
				noReferer($(this).attr("href"));
				return false;
			});
		});

		x.subscribe("live", "visit_online_link", function(data) {
			$("#live_link_" + data.id + " .js-live-clicks").html(data.clicks);
		});

		x.subscribe("live", "remove_online_link", function(data) {
			$("#live_link_" + data.id).slideUp(500);
		});

		resetFileds(false);
		setInterval(function() {
			reloadPage();
		}, 2 * 60 * 1000);

		x.addPageProcessor(":moderator", function(path, params) {
		    if (params) {
			    $(".js-remove-button").removeClass("g-hidden").click(function() {
				    var link_id = $("img", this).attr("alt");
				    $.getJSON("http://"+ location.host +"/mod/removeOnlineLink/"+ link_id +"/");
				    return false;
			    });
	        }
		});
	});

	x.addPageProcessor("news/last_comments", function() {
		var _title = document.title, waiting;
		var previewTimeout, previewActive, previewActiveLink, commentPreview = function(node, clone) {
			clone = clone || false;
			$(".js-cross-link", node).mouseover(function(e) {
				if (!$(this).data("preview_open"))
				{
					var nm = $(this).attr("name").substring(0, 4);
					if (nm && nm != "news")
						return;

					if (!clone && previewActiveLink) {
						$(".b-comment.m-tip").remove();
						$(previewActiveLink).data("preview_open", false);
						previewActiveLink = this;
					} else if (!previewActiveLink)
						previewActiveLink = this;

					previewActive     = true;
					previewTimeout    = clearTimeout(previewTimeout);

					var id = $(this).text().replace(/\D/g, ""), link_ = this;
					$.getJSON("http://"+ location.host +"/news/last_comments/", {id: id}, function(data, status) {
						if (status != "error" && data != false) {
							var tip = $(template("template_comment", data))
								       .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
								       .addClass("m-tip")
								       .attr('id', '')
								       .css({display:'block', width: data.post_preview ? '520px' : '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});
							if (data.post_preview) tip.addClass("m-post-preview").find(".js-comment-id").html("(<em>открывающий пост</em>)");
							$(document.body).append(tip);
							tip.slideUp(0).slideDown(300);
							$(link_).data("preview_open", true);
							commentPreview(tip, true);
						}
					});
				}
				e.stopPropagation();
			});
		};
		$(document.body).mouseover(function(e) {
			if (!previewTimeout && previewActive) {
				previewTimeout = setTimeout(function() {
					$(".b-comment.m-tip").remove();
					$(previewActiveLink).data("preview_open", false);
					previewActiveLink = null;
					previewActive = false;
				}, 400);
			}
		});

		$(window)
			.bind("blur", function() {
				waiting = true;
			})
			.bind("focus", function() {
				waiting = false;
				document.title = _title;
			});

		commentPreview(document);

		x.subscribe("post_last_comments", "add_post_comment", function(data) {
		    $.getJSON("http://"+ location.host +"/news/res/"+ data.post_id +"/getComment/"+ data.id, {"title" : 1}, function(data) {
			    var node = $(template("template_comment", data));
			    $("#placeholder_comment").prepend(node);
			    node.slideUp(0).slideDown(400);

			    if (waiting)
				    document.title = " ★ " + _title + " ★ ";

			    commentPreview(node);
			});
		});

		x.subscribe("post_last_comments", "remove_post_comment", function(data) {
			$("#comment_" + data.id).slideUp(500);
		});

		x.addPageProcessor(":moderator", function(path, params) {
		    if (params) {
			    $(".js-remove-button").removeClass("g-hidden").click(function() {
				    var comment_id = $("img", this).attr("alt");
				    $.getJSON("http://"+ location.host +"/mod/removePostComment/"+ comment_id +"/");
				    return false;
			    });
	        }
		});
	});

    x.addPageProcessor("service/modlog", function() {
        var previewTimeout, previewActive, previewActiveLink, commentPreview = function(node, clone) {
			clone = clone || false;
			$(".js-cross-link", node).mouseover(function(e) {
				if (!$(this).data("preview_open"))
				{
					var nm = $(this).attr("name").substring(0, 4);
					if (nm && nm != "news")
						return;

					if (!clone && previewActiveLink) {
						$(".b-comment.m-tip").remove();
						$(previewActiveLink).data("preview_open", false);
						previewActiveLink = this;
					} else if (!previewActiveLink)
						previewActiveLink = this;

					previewActive     = true;
					previewTimeout    = clearTimeout(previewTimeout);

					var id = $(this).text().replace(/\D/g, ""), link_ = this;
					$.getJSON("http://"+ location.host +"/news/last_comments/", {id: id}, function(data, status) {
						if (status != "error" && data != false) {
							var tip = $(template("template_comment", data))
								       .mouseover(function(e) { previewTimeout = clearTimeout(previewTimeout); e.stopPropagation(); })
								       .addClass("m-tip")
								       .attr('id', '')
								       .css({display:'block', width: data.post_preview ? '520px' : '450px', position: 'absolute', top: e.pageY + 8, left: e.pageX + 8});
							if (data.post_preview) tip.addClass("m-post-preview").find(".js-comment-id").html("(<em>открывающий пост</em>)");
							$(document.body).append(tip);
							tip.slideUp(0).slideDown(300);
							$(link_).data("preview_open", true);
							commentPreview(tip, true);
						}
					});
				}
				e.stopPropagation();
			});
		};
		$(document.body).mouseover(function(e) {
			if (!previewTimeout && previewActive) {
				previewTimeout = setTimeout(function() {
					$(".b-comment.m-tip").remove();
					$(previewActiveLink).data("preview_open", false);
					previewActiveLink = null;
					previewActive = false;
				}, 400);
			}
		});
		commentPreview(document);
    });

	x.addPageProcessor("chat", function() {
	    $(".js-favchatlist").sortable({
	        connectWith: ".js-allchatlist", dropOnEmpty: true, revert: true,
	        update: function(event, ui) {
	            if ($(this).children().length == 0)
	                $(".js-favchatlist-empty").show();
	            else
	                $(".js-favchatlist-empty").hide();

	            var favorites = [];
	            $(".b-chat-rooms_b-room", this).each(function() {
	                var id = this.id.substring(5);
	                favorites.push(id);
	            });

	            $.post(
				    "http://"+ location.host +"/chat/favorites/",
				    {
					    "favorites": favorites.join(",")
				    }
				);
	        }
	    }).disableSelection();

	    $(".js-allchatlist").sortable({
	        connectWith: ".js-favchatlist", dropOnEmpty: true, revert: true,
	        update: function(event, ui) {
	            if ($(this).children().length == 0)
	                $(".js-allchatlist-empty").show();
	            else
	                $(".js-allchatlist-empty").hide();
	        }
	    }).disableSelection();

	    $(".b-chat-rooms_b-chat-group_b-add-hidden").click(function() {
	        var room = prompt("Скопируйте ссылку или идентификатор скрытой комнаты", "");
	        if((matches = /([^\/]{45})/g.exec(room)) != null)
	        {
	            var favorites = [];
	            $(".js-favchatlist .b-chat-rooms_b-room").each(function() {
	                var id = this.id.substring(5);
	                favorites.push(id);
	            });
	            favorites.push(matches[0]);

	            $.post(
				    "http://"+ location.host +"/chat/favorites/",
				    {
					    "favorites": favorites.join(",")
				    },
				    function(data, state) {
				        if (state !== "error")
				            location.reload();
				    }
				);
	        }
	    });

	    x.subscribe("chats", "add_room", function(data) {
			$("#chat_notify").show("fade");
		});

		x.subscribe("chats", "edit_room", function(data) {
		    var node = $("#room_"+ data.room_id);

		    if (node.length == 0 && data["public"]) {
		        $("#chat_notify").show("fade");
		        return;
		    }

		    $(".b-chat-rooms_b-room_b-header a", node).html(data.title);
		    $(".b-chat-rooms_b-room_b-description p", node).html(data.description);

		    if (!data["public"])
		        $(".js-hidden-room-icon", node).removeClass("g-hidden");
		    else
		         $(".js-hidden-room-icon", node).addClass("g-hidden");

		    if (data["password"])
		        $(".js-key-icon", node).removeClass("g-hidden");
		    else
		        $(".js-key-icon", node).addClass("g-hidden");
		});

		x.subscribe("chats", "stats_updated_room", function(data) {
		    $("#room_"+ data.room_id +" .b-chat-rooms_b-room_b-info em").html(ending(data.online, "участник", "участника", "участников"));
		});
	});

	x.addPageProcessor("chat/add", function() {
	    var validateRoom = function(callback) {
			$("#chat_form_error").html("");
			$("#chat_form input, #chat_form textarea").removeClass("g-input-error");

			$.post(
				"http://"+ location.host +"/chat/add/",
				{
					"title":       $("#chat_form [name=title]").val(),
					"controlword": $("#chat_form [name=controlword]").val(),
					"description": $("#chat_form [name=description]").val()
				},
				function(result, status) {
					if (status != "error") {
						if (result.isValid != true)
						{
							var error_strings = [];
							for (var field in result.validationResults)
							if (result.validationResults.hasOwnProperty(field))
							{
								error_strings.push(result.validationResults[field]);
								$("#chat_form [name="+ field +"]").addClass("g-input-error");
							}
							$("#chat_form_error").html(error_strings.join(", "));
							return false;
						}
						callback.call();
					}
				},
				"json"
			);
		};

		$("#chat_form input[name=title]")
			.bind("keyup", function() {
				$("#chat_form_title_length")
					.html(ending($(this).val().length, "символ", "символа", "символов"));
			});

		$("#chat_form input[type=submit]").click(function() {
			validateRoom(function() {
				$("#chat_form").submit();
			});
			return false;
		});
    });


	x.addPageProcessor(/^chat\/[0-9A-z\-]{4,45}/, function(match) {
		var id = $(".b-chat").attr("id").substring(5), channel_id = null, password = false;
		var your_messages = [];
		var _title = document.title, waiting, notifier = null, notify = false;

		if (id) {
		    $(window)
			    .bind("blur", function() {
				    waiting = true;
			    })
			    .bind("focus", function() {
				    waiting = false;
				    if (notifier) notifier.cancel();
				    document.title = _title;
			    });

			(function() {
			        var element = $(".b-chat_b-messages").get(0);
			        var cacheOffsetHeight = parseInt(element.scrollHeight);
			        setInterval(function() {
				        if (parseInt(element.scrollHeight) != cacheOffsetHeight)
					        element.scrollTop += parseInt(element.scrollHeight) - cacheOffsetHeight + 10;

				        cacheOffsetHeight = parseInt(element.scrollHeight);
			        }, 15);

			        window.onresize = (function() {
			            var view_h = document.documentElement.clientHeight;
			            element.style.height = parseInt((view_h / 100) * 50) + "px";

			            return arguments.callee;
			        })();
		    })();

		    x.subscribe("chat_" + id, "stats_updated", function(data) {
		        $(".js-room-statistics").html(ending(data.online, "участник", "участника", "участников"));
		    });

		    x.subscribe("chat_" + id, "edit_room", function(data) {
		        $(".js-room-title").html(data.title);
		    });

		    var lastMessage = [];
	        var addInfoMessage = function(message) {
	                var node = $(template("template_message_info", {"message": message}));
	                $("#placeholder_messages").append(node);
	                node.slideUp(0).slideDown(500);
	            },
	            addErrorMessage = function(message) {
	                var node = $(template("template_message_error", {"message": message}));
	                $("#placeholder_messages").append(node);
	                node.slideUp(0).slideDown(500);
	            },
	            addMessage = function(id, date, message) {
	                var message_re = new RegExp("(&gt;&gt;"+ your_messages.join("|&gt;&gt;") +")\\b", "g");

	                if (your_messages.length && message_re.test(message)) {
	                    message = message.replace(message_re, '<b style="border-bottom:1px dotted #555;">$1</b>');
	                }

	                if (waiting) {
				        if (window.webkitNotifications) {
				            if (window.webkitNotifications.checkPermission() == 0 && notify) {
				                if (notifier) notifier.cancel();
				                notifier = window.webkitNotifications.createNotification("", _title, $(message).text());
				                notifier.show();
				            }
				        }

				        document.title = " ★ " + _title + " ★ ";
				    }

	                var node = $(template("template_message_normal", {"id": id, "date": date, "message": message}));
	                $("#placeholder_messages").append(node);
	                $(node).fadeOut(0).fadeIn(300);
	            },
	            addPasswordMessage = function() {
	                $("#placeholder_messages").append(template("template_message_password", {}));
	                $("#password_form").submit(function() {
	                    $.post(
	                        "http://"+ location.host +"/chat/"+ id +"/",
	                        {
	                            "command":  "checkpassword",
	                            "password": $(".js-room-password").val()
	                        },
	                        function(data, status) {
	                            if (status !== "error") {
	                                if (data) {
	                                    channel_id = data;
	                                    password = $(".js-room-password").val();
	                                    $("#password_form").html("Успешно авторизирован.");
	                                    enterChat();
	                                } else {
	                                    $(".js-room-password").attr("disabled", "disabled").val("Пароль неверный");
	                                    setTimeout(function() {
	                                        $(".js-room-password").attr("disabled", "").val("");
	                                    }, 2000);
	                                }
	                            }
	                        },
	                        "json"
	                    );
	                    return false;
	                });

	            },
	            enterChat = function() {
	                $.getJSON("http://"+ location.host +"/chat/"+ id +"/", {"command": "welcome"}, function(data, status) {
	                    if (status !== "error")
	                    {
	                        addInfoMessage("Добро пожаловать в комнату <b>«"+ data.title +'»</b> (<a href="http://'+ location.host +'/chat/log/'+ id +'/">лог комнаты</a>). <br />Описание: '+ data.description +'<br /><a href="http://1chan.ru/help/rules/#chat">Правила раздела</a>, <a href="http://1chan.ru/help/markup/">разметка сообщений</a>.');
	                        if (data.info)
	                            addInfoMessage(data.info);

	                        x.subscribe("chat_" + channel_id, "message", function(data) {
		                        switch(data.type) {
		                            case 'normal':
		                                addMessage(data.id, data.date, data.message);
		                                break;
		                            case 'info':
		                                addInfoMessage(data.message);
		                                break;
		                            case 'error':
		                                addErrorMessage(data.message);
		                                break;
		                        }
		                    });
		                    x.socket.setCursor("chat_" + channel_id, 0);
		                    x.socket.execute();

		                    $(".js-message-textarea").attr("disabled", "")
			                    .bind("keyup", function(e) {
			                        var lastCode = $(this).data("lastCode");

				                    if ((e.ctrlKey || e.metaKey) && (e.keyCode==10 || e.keyCode==13)) {
					                    $("#message_form").submit();
				                    }

				                    if ((e.ctrlKey || e.metaKey) && (e.keyCode==38)) {
					                    $(this).val(lastMessage.pop());
				                    }

				                    if ((e.keyCode==10 || e.keyCode==13) && (lastCode==10 || lastCode==13)) {
					                    $("#message_form").submit();
				                    }

			                        $(this).data("lastCode", e.keyCode);
			                    })
			                    .one("click", function() {
			                        if (window.webkitNotifications) {
	                                    window.webkitNotifications.requestPermission(function() {
	                                        if (window.webkitNotifications.checkPermission() == 0)
	                                            notify = true;
	                                    });
	                                }
	                            });

			                $(".js-message-submit").attr("disabled", "");

		                    $("#message_form").submit(function() {
		                        $(".js-message-submit").attr("disabled", "disabled");
		                        $.post(
	                                "http://"+ location.host +"/chat/"+ id +"/",
	                                {
	                                    "command":  "message",
	                                    "password":  password || '',
	                                    "message":  $(".js-message-textarea").val()
	                                },
	                                function(data) {
	                                    if (data.error == false) {
	                                        lastMessage.push($(".js-message-textarea").val());
	                                        your_messages.push(data.id);
	                                        $(".js-message-textarea").val("");
	                                    } else
	                                        addErrorMessage(data.errors);

	                                    $(".js-message-submit").attr("disabled", "");
	                                },
	                                "json"
	                           );
		                        return false;
		                    });

		                    setInterval(function() {
		                        $.getJSON("http://"+ location.host +"/chat/"+ id +"/", {"command": "ping"});
		                    }, 1000 * 30);
	                    }
	                });

	                $(".js-message-from-link").live("click", function() {
	                    insertText(">>" + this.title +", ");
	                    return false;
	                });
	            };

	        $.getJSON("http://"+ location.host +"/chat/"+ id +"/", {"command": "enter"}, function(data, status) {
	            if (status !== "error")
	            {
	                if (data == null)
	                    return addErrorMessage("Запрошенный канал не существует.");

	                if (data == false)
	                    return addPasswordMessage();

                    channel_id = data;
	                enterChat();
	            }
	        });
	    }
	});

	x.addPageProcessor("*", function() {
		var to = null,
		    reloadOnlineLinks = function() {
			$.getJSON("http://"+ location.host +"/live/?num=12", function(data, status) {
				if (status != "error") {
					if (data.length == 0) {
						$("#placeholder_link_panel .b-live-entry").remove();
					} else {
						$("#placeholder_link_panel .b-live-entry").remove();
						for(var i in data) {
							var node = $(template("template_link_panel", data[i]));
							$("#placeholder_link_panel").append(node);
						}
					}
				}
			});
			};

		$(".b-live-entry_b-description, .b-blog-entry_b-header a.m-external").click(function(e) {
            var  w = window.open();
            w.document.write('<meta http-equiv="refresh" content="0;url='+ $(this).attr("href") +'">');
            w.document.close();
            return false;
		});

		$("#stats_block")
			.css({"display":"block"}).hide(0);

		$(".b-header-block")
			.mouseover(function() {
				to && (to = clearTimeout(to));
				$("#stats_block").stop(true, true).show(500);
			})
			.mouseenter(function() {
				$.getJSON("http://"+ location.host +"/service/getGlobalStats/", {}, function(data, status) {
				    $("#stats_online").html(data["global_online"]);
			        $("#stats_hosts").html(data["global_unique"]);
			        $("#stats_posts").html(data["global_posts"]);
			        $("#stats_unique_posters").html(data["global_unique_posters"]);
			        $("#stats_speed").html(data["global_speed"]);
			    });
			})
			.mouseleave(function() {
				to = setTimeout(function() {
					$("#stats_block").stop(true, true).hide(500);
				}, 500);
			});

		$(".js-links-panel").mouseover(function() {
				$(".js-close-right-panel").removeClass("g-hidden");
			})
			.mouseleave(function() {
				$(".js-close-right-panel").addClass("g-hidden");
			});

		$(".js-close-right-panel").click(function() {
		    $.getJSON("http://"+ location.host +"/live/linksPanel/", {status: "off"}, function(data, status) {
		        $(".l-right-panel-wrap").remove();
		    });
		    return false;
		});

		$("#ajax_loader").ajaxSend(function() {
			$(this).css({"display":"block"});
		});
		$("#ajax_loader").ajaxComplete(function() {
			$(this).css({"display":"none"});
		});
		$("#ajax_loader").ajaxError(function() {
			$(this).css({"display":"none"});

			$("#ajax_loader_error").css({"display":"block"});
			setTimeout(function() {
				$("#ajax_loader_error").css({"display":"none"});
			}, 2000);
		});

		x.subscribe("global", "add_online_link", function(data) {
			$(".b-top-panel_b-online-link").removeClass("m-disactive");
			reloadOnlineLinks();
		});

		x.subscribe("global", "add_board_post", function(data) {
			var node = $(".js-board-counter");
			node.data("num") || node.data("num", 0);

			var num = node.data("num");
			node.data("num", ++num);

			node.html("+"+ num).hide().show("fade", 500);
		});

		var waiting = false, queue = [], showNotify = function(data, thread) {
			thread = thread || false;
			if (thread)
			{
				var link = data.link;
				notify(
					"Новый тред в разделе &laquo;"+ data.board +"&raquo;:", 
					(data.upload ? '<img src="/'+ data.upload +'" width="80" /> ' : '') + (data.title? data.title+": " : "") + data.text,
					function() {	
						window.open(link);
					}
				);
			}
			else
			{
				var link = data.link;
				notify(
					"Новый пост в треде &laquo;"+ data.title +"&raquo; (раздел "+ data.board +"):", 
					(data.upload ? '<img src="/'+ data.upload +'" width="80" /> ' : '') + data.text,
					function() {	
						window.open(link);
					}
				);
			}
		};

		$(window)
			.bind("blur", function() {
				waiting = true;
			})
			.bind("focus", function() {
				waiting = false;
				if (queue.length)
				{
					for(var i in queue)
					if (queue.hasOwnProperty(i))
					{
						showNotify(queue[i], !("post" in queue[i]));
					}
					queue = [];
				}
			});

		x.subscribe("global", "new_board_thread", function(data) {
			$.getJSON("/service/notifyCheck/"+ data.board +"/"+ data.id);
			if (waiting)
				return queue.push(data);

			showNotify(data, true);
		});

		x.subscribe("global", "new_board_post", function(data) {
			$.getJSON("/service/notifyCheck/"+ data.board +"/"+ data.id);
			if (post_filters.indexOf(data.id) > 0)
				return false;

			if (waiting)
				return queue.push(data);

			showNotify(data, false);
		});

		$.getJSON("/service/notifyGet/", function(message) {
			if (message) {
				var j = 0;
				for(var i in message)
				if (message.hasOwnProperty(i))
				{
					(function(data, j) { setTimeout(function() {
						showNotify(data, !("post" in data));
					}, 10000 * Math.floor(j / 5))})($.parseJSON(message[i]), j++);
				}
			}
		});

		/**
		 * Poo-chan:
		 **/
		var poo_chan = $.cookie("poo") ? 1 : 0, target_page = location.pathname.replace(/\/$/, '').replace(/\//g, '_');

		$(".js-poo-target").draggable({
			revert: true,
			stop: function(e) {
				var p = $(".l-wrap").offset()
				$.post("/service/poo-chan/", {target: target_page, top: e.pageY - 24, left: e.pageX - p.left - 24});
			}
		});

		$(".js-poo-toggle").click(function() {
			if (poo_chan) {
				$(".js-poo").hide();
				$(this).html("Включить каку");
				$.cookie("poo", null, {"path":"/"});
				poo_chan = 0;
				return false;
			}

			$(".js-poo").show();
			$.cookie("poo", true, {"path":"/"});
			$(this).html("Выключить каку");
			poo_chan = 1;
			return false;
		}).html(poo_chan ? "Выключить каку" : "Включить каку");

		x.socket.setCursor("page_"+ target_page, 0);
		x.socket.subscribe("page_"+ target_page, function(data) {
			var el = $("<img src='/img/poo_target.png' width='48' height='50' class='js-poo g-hidden' />");
			el.css({
				"position": "absolute",
				"top":      Math.floor(data.data.top) +"px",
				"left":     Math.floor(data.data.left) +"px",
				"zIndex":   99,
				"dispay":   "none"
			});
			$(".l-wrap").append(el);
			if (poo_chan) $(el).show("bounce", 500);
		});

		(function() {
			var board = $.cookie("homeboard");
			if (board) {
				var el = $(".js-homeboard-select a[name="+ board +"]");
				if (el.length) {
					$("input[name=homeboard]").val(board);
					$(".js-homeboard-icon").attr("src", $("img", el).attr("src"));
				}
			} else {
				var ref = document.referrer;
				if (ref.search("1chan.ru") != -1) return;
				
				$(".js-homeboard-select a").each(function() {
					var board = $(this).attr("name");

					if (ref.search(board) != -1) {
						$.cookie("homeboard", board, {expires: 356, path: "/"});
						$("input[name=homeboard]").val(board);
						$(".js-homeboard-icon").attr("src", $("img", this).attr("src"));
						return false;
					}
				});
			}
		})();

		x.socket.subscribe(document.body.id, function() {});
		x.socket.execute();
	});

	/**
	 * ----------------------------------------------- *
	 */
	$(document).ready(function() {
		x.callPageProcessors(location.pathname.replace(/^\//, '').replace(/\/$/, ''));
		x.callPageProcessors("*");
	});

	/**
	 * ----------------------------------------------- *
	 */
	function ending(num, n1, n2, n5){
		num = num.toString();
		ch = parseInt(num.substr(-1, 1));

		if (ch==1) {
			if (num.length > 1) {
				result = num.substr(-2,1) == 1 ? n5 : n1;
			} else
				result = n1;
		} else if(ch > 1 && ch < 5) {
			if (num.length > 1)
				result = num.substr(-2, 1) == 1 ? n5 : n2;
			else
				result = n2;
		} else {
			result=n5;
		}
		return num +' '+ result;
	};

	var cache = {};
	function template(str, data){
		var fn = !/\W/.test(str) ?
		  cache[str] = cache[str] ||
		    template(document.getElementById(str).value) :

		  new Function("obj",
		    "var p=[],print=function(){p.push.apply(p,arguments);};" +
		    "with(obj){p.push('" +
		    str
		      .replace(/[\r\t\n]/g, " ")
		      .split("<%").join("\t")
		      .replace(/((^|%>)[^\t]*)'/g, "$1\r")
		      .replace(/\t=(.*?)%>/g, "',$1,'")
		      .split("\t").join("');")
		      .split("%>").join("p.push('")
		      .split("\r").join("\\'")
		  + "');}return p.join('');");

		return data ? fn( data ) : fn;
	  };

	function insertText(text)
	{
		var textarea = document.getElementById("comment_form_text");
		if(textarea)
		{
			if(textarea.createTextRange && textarea.caretPos)
			{
				var caretPos=textarea.caretPos;
				caretPos.text=caretPos.text.charAt(caretPos.text.length-1)==" "?text+" ":text;
			}
			else if(textarea.setSelectionRange)
			{
				var start=textarea.selectionStart;
				var end=textarea.selectionEnd;
				textarea.value=textarea.value.substr(0,start)+text+textarea.value.substr(end);
				textarea.setSelectionRange(start+text.length,start+text.length);
			}
			else
			{
				textarea.value+=text+" ";
			}
			textarea.focus();
		}
	};

	function notify(title, text, click, silent)
	{
		title  = title  || "";
		text   = text   || "";
		click  = click  || function() { $(this).remove(); };
		silent = silent || false;

		var node = $("<div class='b-notifier g-hidden g-clearfix'>")
				.html("<strong>"+ title +"</strong><p>"+ text +"</p>")
				.click(click)
				.hide("clip", 0);

		$(".js-notifiers").append(node);
		node.show("clip", 500, 
				function() {
					var t = this;
					setTimeout(function() {
						$(t).hide("fade", 1000, function() { $(this).remove(); });
					}, 10000);
				}
			)
	}

	window.authorize = function(key, hash)
	{
		$.post("http://"+ location.host +"/auth", {key: key, hash: hash || 0}, function(data, status) {
			if (status != "error") {
				if (data.authorized == true) {
					x.callPageProcessors(":moderator", !!hash);
				}
			}
		}, "json");
	}
})();
