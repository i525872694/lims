function treeSvg(flare_Data,width,height,dep,parent,tagUi){
    
    var m = [20, 100, 20, 100],
        w = width - m[1] - m[3],
        h = height - m[0] - m[2],
        i = 0,
        root;


    var tree = d3.layout.tree()
        .size([h, w]);

    var diagonal = d3.svg.diagonal()
        .projection(function(d) { return [d.y, d.x]; });

    var vis = d3.select("#"+parent).append("svg:svg")
        .attr("width", w + m[1] + m[3])
        .attr("height", h + m[0] + m[2])
    .append("svg:g")
        .attr("transform", "translate(" + m[3] + "," + m[0] + ")");

    var tree_node_info = {
        now_node_father_is_check:false,//刚点击的结点的上级是否有被选中
        now_node_child_is_check:false,//刚点击的结点的子级是否有被选中
        now_node_father_checked_id:false,//紧挨着的被选中的父级id
        select_node:{},//记录当前节点

    };
 
    if(tagUi){
        tree_node_info.typeUi=tagUi;
    }else{
         tree_node_info.typeUi=0;
    }
    get_flare_data(flare_Data);

    return tree_node_info;

    function get_flare_data(json) {
        root = json;
        root.x0 = h / 2;
        root.y0 = 0;
        function toggleAll(d) {
            if (d._children) {
                d.children.forEach(toggleAll);
                toggle(d);
            }
        }
        // Initialize the display to show a few nodes.
        root.children.forEach(toggleAll);
        update(root);
    };

    function update(source) {
    var duration = d3.event && d3.event.altKey ? 5000 : 500;

    // Compute the new tree layout.

    var nodes = tree.nodes(root).reverse();
    // Normalize for fixed-depth.
    nodes.forEach(function(d) { d.y = d.depth * dep; });

    // Update the nodes…
    var node = vis.selectAll("g.node")
        .data(nodes, function(d) { return d.id || (d.id = ++i); });


    var nodeEnter = node.enter().append("svg:g")
        .attr("class", "node")
        .attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
        .on("click", function(d) {
            tree_node_info.now_node_father_is_check    = false;////刚点击的结点的上级是否有被选中
            tree_node_info.now_node_child_is_check     = false;//刚点击的结点的子级是否有被选中
            tree_node_info.now_node_father_checked_id  = false;//紧挨着的被选中的父级id
            if(!d.check){//当前未选中,现在点击要选中

                   // d3.select('#node-'+parent+'-'+d.id).style("stroke",'red').style("stroke-width","3px").style("r",'10px');
                    d3.select('#node-'+parent+'-'+d.id).style("fill",'#26b0c3');
                    d.check = true;
                    tree_node_info.select_node[d.id]=d;
                    

                    if( tree_node_info.typeUi && d.parent){
                           
                           tree_node_info.now_node_father_is_check=true;
                        
                        var temp_dom = d.parent;
                            do{
                    
                                temp_dom.check=true;
                                d3.select('#node-'+parent+'-'+temp_dom.id).style("fill",'#26b0c3');
                                
                                tree_node_info.select_node[temp_dom.id]=temp_dom;

                                if(!temp_dom.parent) break;

                                 temp_dom = temp_dom.parent;
                                 
                                
                            }while(1);
                            delete temp_dom;
                            delete p_dom;
                    }else{
                        check_now_node_father_is_check(d)
                    }
                   
                   check_now_node_child_is_check(d);

                    //tree_node_info.now_node_father_is_check =false;
                    //tree_node_info.now_node_child_is_check=false;
                    //check_now_node_father_is_check(d);
                    //check_now_node_child_is_check(d);
            }else{//当前已经选中,现在点击要取消选中

                d.check = false;
               // d3.select('#node-'+parent+'-'+d.id).style("stroke",'steelblue').style("stroke-width","1.5px").style("r",'8px');
                d3.select('#node-'+parent+'-'+d.id).style("fill",'#fff');
                delete tree_node_info.select_node[d.id];
                
                
                if( tree_node_info.typeUi && d.children){
                    
                    tree_node_info.now_node_child_is_check=false;

                    digui_cancel_celcall(d);
                    function digui_cancel_celcall(v_node){
                            if(v_node.children){
                                for(var i in v_node.children)
                                {
                                    var v =  v_node.children[i];
                                   
                                    v.check = false;
                                    d3.select('#node-'+parent+'-'+v.id).style("fill",'#fff');
                                    delete tree_node_info.select_node[v.id];
                                    
                                    if(v.children){
                                        digui_cancel_celcall(v);
                                    }
                                }
                            }
                    }
                }else{
                     check_now_node_child_is_check(d);
                }

                check_now_node_father_is_check(d);
            }
            clickNodetrigger(d);
        }); 

    nodeEnter.append("svg:circle")
        .attr("id", function(d) { return 'node-'+parent+'-'+d.id; })
        .style("fill",  function(d) {
            if( d.check){
                tree_node_info.select_node[d.id]=d;
            }

            return d.check ? "#26b0c3" : "#fff";
        })
        // .style("stroke",  function(d) {
        //      if( d.check){
        //          tree_node_info.select_node[d.id]=d;
        //      }
        //
        //      return d.check ? "red" : "steelblue";
        // })
        // .style("stroke-width",  function(d) { return d.check ? "3px" : "1.5px"; })
        .style("stroke-width",  function(d) { return  "1.5px"; })
        // .style("r",  function(d) { return d.check ? "10px" : "8px"; })
        .style("r",  function(d) { return "10px" })

    nodeEnter.append("svg:text")
        .attr("x", function(d) { return d.children || d._children ? -15 : 15; })
        .attr("dy", ".35em")
        .attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
        .text(function(d) { return d.name; })
        .style("fill-opacity", 1e-6);


    // Transition nodes to their new position.
    var nodeUpdate = node.transition()
        .duration(duration)
        .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

    nodeUpdate.select("circle")
        .attr("r", 8)
        .style("fill", function(d) {
            return d.check ? "#26b0c3" : "#fff";
        });

    nodeUpdate.select("text")
        .style("fill-opacity", 1);

    // // Transition exiting nodes to the parent's new position.
    // var nodeExit = node.exit().transition()
    //     .duration(duration)
    //     .attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
    //     .remove();

    // nodeExit.select("circle")
    //     .attr("r", 1e-6);

    // nodeExit.select("text")
    //     .style("fill-opacity", 1e-6);

    // Update the links…
    var link = vis.selectAll("path.link")
        .data(tree.links(nodes), function(d) { return d.target.id; })
        .style("fill","none");

    // Enter any new links at the parent's previous position.
    link.enter().insert("svg:path", "g")
        .attr("class", "link")
        .attr("d", function(d) {
            var o = {x: source.x0, y: source.y0};
            return diagonal({source: o, target: o});
        })
        .transition()
        .duration(duration)
        .attr("d", diagonal);

    // Transition links to their new position.
    link.transition()
        .duration(duration)
        .attr("d", diagonal);

        // Transition exiting nodes to the parent's new position.
        link.exit().transition()
            .duration(duration)
            .attr("d", function(d) {
                var o = {x: source.x, y: source.y};
                return diagonal({source: o, target: o});
            })
        .remove();

        // Stash the old positions for transition.
        nodes.forEach(function(d) {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    }

    // Toggle children.
    function toggle(d) {
        if (d.children) {
            d._children = d.children;
            d.children = null;
        } else {
            d.children = d._children;
            d._children = null;
        }
    }

    //判断刚点击的节点的上级是否选中
    function check_now_node_father_is_check(node)
    {
        if(!node.parent)  return false;//没有父级，直接跳出
       var check_node = node.parent;
        do {

            if(check_node.check)
            {
                tree_node_info.now_node_father_checked_id  = check_node.id;
                tree_node_info.now_node_father_is_check=true;
                break;//有一个为真即可
            }
            
            if(!check_node.parent)  break;//没有父级，直接跳出
            
            check_node =check_node.parent;

        } while (!tree_node_info.now_node_father_is_check  );
    }

    //判断刚点击的节点的子级是否选中
    function check_now_node_child_is_check(node)
    {
        var check_node = node;
        if(!check_node.children)  return false;//没有子级，直接跳出
        check_node.children.forEach(function(v,k){
            if(v.check)
            {
                tree_node_info.now_node_child_is_check=true;

            }else{
                check_now_node_child_is_check(v)
            }
        })
    }
}