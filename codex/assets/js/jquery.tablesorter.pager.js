(function($) {
    $.extend({
        tablesorterPager: new function() {
            
            function updatePageDisplay(c) {
                var s = $(c.cssPageDisplay,c.container).val((c.page+1) + c.seperator + c.totalPages);    
            }
            
            function setPageSize(table,size) {
                var c = table.config;
                c.size = size;
                c.totalPages = Math.ceil(c.totalRows / c.size);
                c.pagerPositionSet = false;
                
                if(c.ajax){
                    
                    $.post(c.controllerLink,{query:c.query,field:c.field,per_page:c.size,page:c.page},function(data){
                        if(data){
                            
                            $("#main-table tbody").html(data); 
                            $("#main-table").trigger("update"); 
                            
                            
                            updatePageDisplay(c);
                            renderTable(table);
                        }
                    });
                    
                    
                }else{
                    moveToPage(table);
                    fixPosition(table);
                }
                
            }
            
            function fixPosition(table) {
                var c = table.config;
                if(!c.pagerPositionSet && c.positionFixed) {
                    var c = table.config, o = $(table);
                    c.pagerPositionSet = true;
                }
            }
            
            function moveToFirstPage(table) {
                var c = table.config;
                c.page = 0;
                if(c.ajax){
                    
                    $.post(c.controllerLink,{query:c.query,field:c.field,per_page:c.size,page:c.page},function(data){
                        if(data){
                            
                            $("#main-table tbody").html(data); 
                            $("#main-table").trigger("update"); 
                            //var sorting = [[1,0]]; 
                            //$("#main-table").trigger("sorton",[sorting]);
                            
                            
                            updatePageDisplay(c);
                            renderTable(table);
                        }
                    });
                    
                    
                }else{
                    
                    moveToPage(table);
                }
            }
            
            function moveToLastPage(table) {
                var c = table.config;
                c.page = (c.totalPages-1);
                
                if(c.ajax){
                    
                    $.post(c.controllerLink,{query:c.query,field:c.field,per_page:c.size,page:c.page},function(data){
                        if(data){
                            
                            $("#main-table tbody").html(data); 
                            $("#main-table").trigger("update"); 
                            //var sorting = [[1,0]]; 
                            //$("#main-table").trigger("sorton",[sorting]);
                            
                            
                            updatePageDisplay(c);
                            renderTable(table);
                        }
                    });
                    
                    
                }else{
                    
                    moveToPage(table);
                }
            }
            
            function moveToNextPage(table) {
                var c = table.config;
                c.page++;
                if(c.page >= (c.totalPages-1)) {
                        c.page = (c.totalPages-1);
                    }
                if(c.ajax){
                    
                    $.post(c.controllerLink,{query:c.query,field:c.field,per_page:c.size,page:c.page},function(data){
                        if(data){
                            
                            $("#main-table tbody").html(data); 
                            $("#main-table").trigger("update"); 
                            //var sorting = [[1,0]]; 
                            //$("#main-table").trigger("sorton",[sorting]);
                            
                            
                            updatePageDisplay(c);
                            renderTable(table);
                        }
                    });
                    
                    
                }else{
                    
                    moveToPage(table);
                }
            }
            
            function moveToPrevPage(table) {
              var c = table.config;
                    c.page--;
                    if(c.page <= 0) {
                            c.page = 0;
                        }
                    if(c.ajax){
                        
                        $.post(c.controllerLink,{query:c.query,field:c.field,per_page:c.size,page:c.page},function(data){
                            if(data){
                                $("#main-table tbody").html(data); 
                                $("#main-table").trigger("update"); 
                                //var sorting = [[1,0]]; 
                                //$("#main-table").trigger("sorton",[sorting]);
                                
                                updatePageDisplay(c);
                                renderTable(table);
                            }
                        });
                        
                        
                    }else{
                        
                        moveToPage(table);
                }
            }
                        
            function moveToPage(table) {
                var c = table.config;
                if(c.page < 0 || c.page > (c.totalPages-1)) {
                    c.page = 0;
                }
                
                renderTable(table,c.rowsCopy);
            }
            
            function renderTable(table,rows) {
                
                var c = table.config;
                if(rows)
                    var l = rows.length;
                var s = (c.page * c.size);
                var e = (s + c.size);
                
                
                if(e > rows.length ) {
                    e = rows.length;
                }
                    
                var tableBody = $(table.tBodies[0]);
                
                // clear the table body
                $.tablesorter.clearTableBody(table);
                
               if(c.ajax){
                  
                     for(var i = 0; i < c.size; i++) {
                        
                        var o = rows[i];
                        
                        var l = (o)?o.length:0;
                        
                        for(var j=0; j < l; j++) {
                            tableBody[0].appendChild(o[j]);
                        }
                    }
               }else{
                    for(var i = s; i < e; i++) {
                        
                        //tableBody.append(rows[i]);
                        
                        var o = rows[i];
                        var l = o.length;
                        
                        for(var j=0; j < l; j++) {
                            tableBody[0].appendChild(o[j]);
                        }
                    }
               }
                fixPosition(table,tableBody);
                
                $(table).trigger("applyWidgets");
                
                if( c.page >= c.totalPages ) {
                    moveToLastPage(table);
                }
                
                updatePageDisplay(c);
            }
            
            this.appender = function(table,rows) {
                
                var c = table.config;
                
                c.rowsCopy = rows;
                if(!c.ajax)
                    c.totalRows = rows.length;
                c.totalPages = Math.ceil(c.totalRows / c.size);
                
                renderTable(table,rows);
            };
            
            this.defaults = {
                size: 10,
                offset: 0,
                page: 0,
                totalRows: 0,
                totalPages: 0,
                container: null,
                cssNext: '.next',
                cssPrev: '.prev',
                cssFirst: '.first',
                cssLast: '.last',
                cssPageDisplay: '.pagedisplay',
                cssPageSize: '.pagesize',
                seperator: "/",
                positionFixed: true,
                appender: this.appender,
                controllerLink: '',
                ajax: false
            };
            
            this.construct = function(settings) {
                
                return this.each(function() {    
                    
                    config = $.extend(this.config, $.tablesorterPager.defaults, settings);
                    
                    var table = this, pager = config.container;
                
                    $(this).trigger("appendCache");
                    
                    config.size = parseInt($(".pagesize",pager).val());
                    
                    if(config.ajax)
                        updatePageDisplay(config)
                    
                    $(config.cssFirst,pager).click(function() {
                        moveToFirstPage(table);
                        return false;
                    });
                    
                    $(config.cssNext,pager).click(function() {
                        moveToNextPage(table);
                        return false;
                    });
                    $(config.cssPrev,pager).click(function() {
                        moveToPrevPage(table);
                        return false;
                    });
                    $(config.cssLast,pager).click(function() {
                        moveToLastPage(table);
                        return false;
                    });
                    $(config.cssPageSize,pager).change(function() {
                        setPageSize(table,parseInt($(this).val()));
                        return false;
                    });
                });
            };
            
        }
    });
    // extend plugin scope
    $.fn.extend({
        tablesorterPager: $.tablesorterPager.construct
    });
    
})(jQuery);                