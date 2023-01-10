var config = {
    map: {
        "*": {
            qTip: "Accuride_PageBuilder/lib/qtip/jquery.qtip.min",
            rwdImageMaps: "Accuride_PageBuilder/lib/rwdImageMaps/jquery.rwdImageMaps.min",
            fancybox: "Lof_All/lib/fancybox/jquery.fancybox.pack"
        }
    },
    shim: {
        'Accuride_PageBuilder/lib/qtip/jquery.qtip.min': {
            'deps': ['jquery']
        },
        'Accuride_PageBuilder/lib/rwdImageMaps/jquery.rwdImageMaps.min': {
            'deps': ['jquery']
        }
    }
};