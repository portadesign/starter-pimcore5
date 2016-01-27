pimcore.registerNS("pimcore.treenodelocator.x");

pimcore.treenodelocator.showInTree = function(element, elementType, button) {

        button.disable();

        Ext.Ajax.request({
            url: "/admin/element/type-path",
            params: {
                id: element.id,
                type: elementType
            },
            success: function (button, response) {
                try {
                    var res = Ext.decode(response.responseText);
                    if (res.success) {
                        Ext.getCmp("pimcore_panel_tree_" + elementType + "s").expand();
                        var tree = pimcore.globalmanager.get("layout_" + elementType + "_tree");
                        element.data.typePath = res.typePath;
                        element.data.idPath = res.idPath;
                        pimcore.treenodelocator.searchInTree(element, elementType, tree.tree, res.idPath, null, button);
                    }
                } catch (e) {
                    console.log(e);
                    pimcore.treenodelocator.showError(null, null);
                }

            }.bind(this, button)
        });
}


pimcore.treenodelocator.reportDone = function(element, elementType, button) {
    if (element) {
        pimcore.helpers.removeTreeNodeLoadingIndicator(elementType, element.id);
        var tree = element.getOwnerTree();
        var view = tree.getView();
        view.focusRow(element);
    }
    button.enable();
}

pimcore.treenodelocator.searchInTree = function(element, elementType, tree, path, callback, button) {
    try {

        var initialData = {
            tree: tree,
            path: path,
            callback: callback
        };

        tree.selectPath(path, null, '/', function (success, node) {
            if(!success) {
                try {
                    var lastExpandedNode = pimcore.treenodelocator.getLastExpandedNode(path, tree);
                    lastExpandedNode.expand();
                    pimcore.treenodelocator.getDirection(lastExpandedNode, element, elementType, null, button);
                } catch (e) {
                    console.log(e);
                    pimcore.treenodelocator.showError(lastExpandedNode, lastExpandedNode.data.elementType);
                }
            } else {
                pimcore.treenodelocator.reportDone(null, null,  button);
                if(typeof initialData["callback"] == "function") {
                    initialData["callback"]();
                }
            }
        }.bind(this));

    } catch (e) {
        console.log(e);
        pimcore.treenodelocator.showError(null, null);
    }
}

pimcore.treenodelocator.getDirection = function(node, element, elementType, searchData, button) {
    if (!searchData) {
        // new level
        var pagingData = node.pagingData;
        var pageCount = 1;
        if (pagingData) {
            var page = (pagingData.offset / pagingData.total) + 1;
            pageCount = Math.ceil(pagingData.total / pagingData.limit);
        }

        var searchData = {
            minPage : 1,
            maxPage : pageCount
        }
    }

    var childNodes = node.childNodes;
    var childCount = childNodes.length;

    var nodePath = node.getPath();
    var nodeParts = nodePath.split("/");

    if (elementType == "asset") {
        fullPath = element.data.path + element.data.filename;
    } else {
        fullPath = element.data.general.fullpath;
    }

    var elementParts = fullPath.split("/");
    var elementKey = elementParts[nodeParts.length - 1];

    var typePath = element.data.typePath;
    var typeParts = typePath.split("/");
    var eType = typeParts[nodeParts.length];

    var idPath = element.data.idPath;

    if (idPath == nodePath) {
        var tree = node.getOwnerTree();
        tree.selectPath(idPath);
        pimcore.treenodelocator.reportDone(node, node.data.elementType, button);
        return;
    }

    var idParts = idPath.split("/");
    var elementId = idParts[nodeParts.length];

    // check if already a child
    for (i = 0; i < childCount; i++) {
        var childNode = childNodes[i];
        var childId = childNode.id;
        if (childId == elementId) {
            if (nodePath != idPath) {
                childNode.expand();
                var tree = childNode.getOwnerTree();
                tree.getSelectionModel().select(childNode);
                var view = tree.getView();
                view.focusRow(childNode);
                childNode.expand(false, pimcore.treenodelocator.reloadComplete.bind(this, childNode, element, elementType, null, button));
            } else {
                var tree = node.getOwnerTree();
                tree.selectPath(idPath);
            }
            return;
        }
    }

    var firstFolderChild = null;
    var lastFolderChild = null;
    var firstelementChild = null;
    var lastelementChild = null;

    for (i = 0; i < childCount; i++) {
        var childNode = childNodes[i];
        if (childNode.data.type == "folder") {
            lastFolderChild = childNode;
            if (!firstFolderChild) {
                firstFolderChild = childNode;
            }
        }

        if (childNode.data.type != "folder") {
            lastelementChild = childNode;
            if (!firstelementChild) {
                firstelementChild = childNode;
            }
        }
    }

    // we are looking for type elementType
    var direction = 0;
    var firstKey = null;
    var lastKey = null;

    if (eType == "folder") {
        if (firstFolderChild && elementKey < firstFolderChild.data.text) {
            direction = -1;
        } else if (lastFolderChild && elementKey > lastFolderChild.data.text) {
            direction = 1;
        } else if (firstelementChild) {
            direction = -1;
        }
    } else {
        if (lastFolderChild) {
            direction = 1;
        } else if (firstelementChild && elementKey < firstelementChild.data.text) {
            direction = -1;
        } else if (lastelementChild && elementKey > lastelementChild.data.text) {
            direction = 1;
        }
    }

    var pagingData = node.pagingData;
    if (!pagingData) {
        pimcore.treenodelocator.showError(node, node.data.elementType);
        return;
    }

    var activePage = Math.ceil(pagingData.offset / pagingData.limit) + 1;
    var pageCount = Math.ceil(pagingData.total / pagingData.limit);


    if (direction == -1) {
        searchData.maxPage = activePage - 1;
        newPage = (searchData.minPage + searchData.maxPage) / 2;
        pimcore.treenodelocator.switchToPage(node, newPage, element, elementType, searchData, button);
    } else if (direction == 1) {

        searchData.minPage = activePage + 1;
        newPage = (searchData.minPage + searchData.maxPage) / 2;
        pimcore.treenodelocator.switchToPage(node, newPage, element, elementType, searchData, button);
    } else {
        pimcore.treenodelocator.reportDone(node, node.data.elementType, button);
    }
}

pimcore.treenodelocator.reloadComplete = function(node, element, elementType, searchData, button) {
    try {
        pimcore.treenodelocator.getDirection(node, element, elementType, searchData, button);
    } catch (e) {
        console.log(e);
        pimcore.treenodelocator.showError(node, node.data.elementType);
    }
}

pimcore.treenodelocator.switchToPage = function(node, pageNumber, element, elementType, searchData, button){
    try {
        pageNumber = Math.floor(pageNumber);

        if (pageNumber < 1) {
            pimcore.treenodelocator.reportDone(node, node.data.elementType, button);
            return;
        }

        var pagingData = node.pagingData;

        var offset = pagingData.limit * (pageNumber - 1);
        node.pagingData.offset = offset;

        var store = node.getTreeStore();

        var proxy = store.getProxy();

        proxy.setExtraParam("start", offset);

        pimcore.helpers.addTreeNodeLoadingIndicator(node.data.elementType, node.id);

        store.reload({
            node: node,
            callback: pimcore.treenodelocator.reloadComplete.bind(this, node, element, elementType, searchData, button)
        });
    } catch (e) {
        console.log(e);
        pimcore.treenodelocator.showError(node, node.data.elementTyoe);
    }
}


pimcore.treenodelocator.getLastExpandedNode = function(path, tree) {
    var ids = path.split("/");
    var arrayLength = ids.length;
    var store = tree.getStore();
    var lastExpandedId = ids[1];
    var lastExpandedNode = store.getNodeById(lastExpandedId);

    return lastExpandedNode;
}

pimcore.treenodelocator.showError = function(element, elementType) {
    if (element) {
        pimcore.helpers.removeTreeNodeLoadingIndicator(elementType, element.id);
    }
    Ext.MessageBox.alert(t("error"), t("not_possible_with_paging"));
}
