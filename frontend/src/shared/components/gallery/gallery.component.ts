import {Component, Input, Output, Inject, EventEmitter, HostListener, SimpleChanges, ViewChild} from '@angular/core';
import {CallbackHandlerService} from '../../services';
import {GalleryGridComponent} from './gallery-grid.component';

@Component({
    selector: 'gallery',
    template: require('./gallery.component.html'),
    styles: [require('./gallery.component.css').toString()],
})
export class GalleryComponent {
    @ViewChild('galleryGridComponent') galleryGridComponent:GalleryGridComponent;

    @Input() items:Array<any> = [];
    @Input() defaultItemId:string;
    @Input() onLoadMoreCallback:any;
    @Input() showCloseButton:boolean = true;
    @Input() showEditButton:boolean = true;
    @Input() showDeleteButton:boolean = false;
    @Output() onOpenItem:EventEmitter<any> = new EventEmitter<any>();
    @Output() onCloseItem:EventEmitter<any> = new EventEmitter<any>();
    @Output() onEditItem:EventEmitter<any> = new EventEmitter<any>();
    @Output() onDeleteItem:EventEmitter<any> = new EventEmitter<any>();

    private openedItem:any;
    private openedItemIndex:any;
    private openedItemIsLoaded:boolean;

    constructor(@Inject(CallbackHandlerService) private callbackHandler:CallbackHandlerService) {
    }

    ngOnInit() {
        this.resetOpenedItem();
    }

    ngOnChanges(changes:SimpleChanges) {
        // We will view default item only on the first load of items.
        // This is a buggy piece of code. Be aware when making a changes.
        if (this.defaultItemId && changes['items'] && !changes['items'].previousValue.length) {
            this.viewItemById(this.defaultItemId);
        }
    };

    @HostListener('document:keydown', ['$event'])
    onDocumentKeyDown = (event:KeyboardEvent) => {
        if (this.openedItem) {
            switch (event.key) {
                case 'Escape':
                    return this.closeItem();
                case 'ArrowLeft':
                    return this.viewPrevItem();
                case 'ArrowRight':
                    return this.viewNextItem(true);
            }
        }
    };

    reset = () => {
        this.resetOpenedItem();
        this.galleryGridComponent.resetGridRowItems();
    };

    setOpenedItem = (item:any, index:number):Promise<any> => {
        this.openedItem = item;
        this.openedItemIndex = index;
        return new Promise((resolve) => {
            let image = new Image;
            let loaded = false;
            image.onload = () => {
                loaded = true;
                this.openedItemIsLoaded = true;
                resolve();
            };
            setTimeout(() => (this.openedItemIsLoaded = loaded), 400);
            image.src = item.thumbnails.large.absolute_url;
        }).then(() => this.onOpenItem.emit(this.openedItem));
    };

    resetOpenedItem = ():void => {
        this.openedItem = null;
        this.openedItemIndex = null;
        this.openedItemIsLoaded = false;
    };

    getOpenedItem = ():any => {
        return this.openedItem;
    };

    setItems = (items:Array<any>):void => {
        this.items = items;
    };

    getItems = ():Array<any> => {
        return this.items;
    };

    viewItemById = (id:string):void => {
        this.items.some((item:any, index:number) => {
            if (item.id == id && index != this.openedItemIndex) {
                this.setOpenedItem(item, index);
                return true;
            } else if (index === this.items.length - 1) {
                this.loadMoreItems().then((items:Array<any>) => {
                    this.viewItemById(id);
                });
            }
            return false;
        });
    };

    viewItem = (item:any):void => {
        let id = item.id;
        this.items.some((item:any, index:number) => {
            if (item.id == id && index != this.openedItemIndex) {
                this.setOpenedItem(this.items[index], index);
                return true;
            }
            return false;
        });
    };

    viewPrevItem = ():void => {
        let prevItemIndex = this.openedItemIndex - 1;
        if (this.items[prevItemIndex]) {
            this.viewItem(this.items[prevItemIndex]);
        }
    };

    viewNextItem = (loadMoreIfNotExist:boolean):void => {
        let nextItemIndex = this.openedItemIndex + 1;
        if (this.items[nextItemIndex]) {
            this.viewItem(this.items[nextItemIndex]);
        } else if (loadMoreIfNotExist) {
            this.loadMoreItems().then((items:Array<any>) => {
                this.viewNextItem(false)
            });
        }
    };

    loadMoreItems = ():Promise<Array<any>> => {
        return this.callbackHandler
            .resolveCallback(this.onLoadMoreCallback)
            .then((items:Array<any>) => {
                this.setItems(items);
                return items;
            });
    };

    closeItem = ():void => {
        this.onCloseItem.emit(this.openedItem);
        this.resetOpenedItem();
    };

    editItem = ():void => {
        this.onEditItem.emit(this.openedItem);
    };

    deleteItem = ():void => {
        this.onDeleteItem.emit(this.openedItem);
    };
}
