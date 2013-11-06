/**
 * "Get Path" jQuery plugin.
 *
 * This should get a unique CSS selector path to the selected element.
 *
 * @see http://stackoverflow.com/questions/2068272/getting-a-jquery-selector-for-an-element
 * @see http://paste.blixt.org/297640
 *
 * @return string CSS selector path to this element
 */
jQuery.fn.getPath = function () {
    if (this.length != 1) throw 'Requires one element.';

    var path, node = this;
    while (node.length) {
        var realNode = node[0], name = realNode.localName;
        if (!name) break;

        name = name.toLowerCase();
        if (realNode.id) {
            // As soon as an id is found, there's no need to specify more.
            return name + '#' + realNode.id + (path ? '>' + path : '');
        } else if (realNode.className) {
            name += '.' + realNode.className.split(/\s+/).join('.');
        }

        var parent = node.parent(), siblings = parent.children(name);
        if (siblings.length > 1) name += ':eq(' + siblings.index(node) + ')';
        path = name + (path ? '>' + path : '');

        node = parent;
    }

    return path;
};