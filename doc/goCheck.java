package cn.laysoft.gocheck;

import java.util.ArrayList;

public class GoCheck {
	//public static boolean[][] flags;
	//public static int[] result;
	//public static int[] count;
	//public static int[][] arrows = { { -1, -1 }, { -1, 0 }, { -1, 1 }, { 0, -1 }, { 0, 1 }, { 1, -1 }, { 1, 0 }, { 1, 1 } };

	public static void main(String[] args) {
		for (int i = 0; i < args.length; i++) {
			int width = 0;
			int height = 0;
			width = height = Integer.parseInt(args[i]);
			new GoCheck(width, height).run();
		}
	}


	private int width = 0;
	private int height = 0;
	private ArrayList<ArrayList<Boolean>> flag = new ArrayList<ArrayList<Boolean>>();
	private ArrayList<ArrayList<ArrayList<Arrow>>> chance = new ArrayList<ArrayList<ArrayList<Arrow>>>();
	private ArrayList<ArrayList<Point>> result = new ArrayList<ArrayList<Point>>();

	public GoCheck(int size) {
		this(size, size);
	}

	public GoCheck(int width, int height) {
		this.width = width;
		this.height = height;
		this.init();
	}
	private void init() {
		for (int i = 0; i < this.height; i++) {
			ArrayList<Boolean> f = new ArrayList<Boolean>();
			ArrayList<ArrayList<Arrow>> a = new ArrayList<ArrayList<Arrow>>();
			for (int j = 0; j < this.width; j++) {
				f.add(false);
				int minRow = 0;
				int minCol = 0;
				int maxRow = this.height - 1;
				int maxCol = this.width - 1;
				ArrayList<Arrow> b = new ArrayList<Arrow>();
				if(i != minRow && j != minCol) {
					b.add(Arrow.UP_LEFT);
				}
				if(i != minRow) {
					b.add(Arrow.UP_CENTER);
				}
				if(i != minRow && j != maxCol) {
					b.add(Arrow.UP_RIGHT);
				}
				if(j != minCol) {
					b.add(Arrow.MIDDLE_LEFT);
				}
				if(j != maxCol) {
					b.add(Arrow.MIDDLE_RIGHT);
				}
				if(i != maxRow && j != minCol) {
					b.add(Arrow.BOTTOM_LEFT);
				}
				if(i != maxRow) {
					b.add(Arrow.BOTTOM_CENTER);
				}
				if(i != maxRow && j != maxCol) {
					b.add(Arrow.BOTTOM_RIGHT);
				}
				a.add(b);
			}
			this.chance.add(a);
			this.flag.add(f);
		}
	}

	@SuppressWarnings("unchecked")
	public void run() {
		//boolean has = true;
		//long total = this.width * this.height;
		Counter counter = new Counter();
		Cursor cursor = new Cursor(0, 0);
		ArrayList<Arrow> chance = this.chance.get(0).get(0);
		ArrayList<Point> finished = new ArrayList<Point>();
		
		this.flag.get(0).set(0, true);
		ArrayList<ArrayList<Boolean>> flag = this.flag;
		finished.add(cursor.toPoint());
		for (int i = 0; i < chance.size(); i++) {
			//cursor = cursor.toCursor();
			Arrow arrow = chance.get(i);
			Cursor newcursor = cursor.move(arrow);
			//newFinished.add(newcursor.toPoint());
			//counter.increase();
			if(this.valid(newcursor, counter, finished, flag)) {
				ArrayList<ArrayList<Boolean>> newflag = (ArrayList<ArrayList<Boolean>>) this.cloneArrayList(flag);//.clone();
				ArrayList<Point> newFinished = (ArrayList<Point>) this.cloneArrayList(finished);//.clone();
				newflag.get(newcursor.getY()).set(newcursor.getX(), true);
				newFinished.add(newcursor.toPoint());
				this.recur(newcursor, counter, newFinished, newflag);
			}
			//this.recur(newcursor, counter, newFinished, newflag);
		}
		/*while (this.recur(cursor, counter)) {
			while (this.result.size() < total) {
				//this.result.
				Random r = new Random();
				int num = r.nextInt(8);
				Arrow[] arrows = Arrow.values();
				Arrow arrow = arrows[num];
				cursor.move(arrow);
				System.out.println("x:" + arrow.getX() + ";y:" + arrow.getY() + ";cursor:" + cursor.toString());
				this.result.add(1);
			}
			counter++;
		}*/
		System.out.println("counter:" + counter.get());
	}
	
	@SuppressWarnings("unchecked")
	private void recur(Cursor cursor, Counter counter, ArrayList<Point> finished, ArrayList<ArrayList<Boolean>> flag) {
		if(finished.size() == this.width * this.height) {
			counter.increase();
			System.out.println("----finished:" + finished);
			this.result.add(finished);
		} else {
			int x = cursor.getX();
			int y = cursor.getY();
			ArrayList<Arrow> chance = this.chance.get(y).get(x);
			for (int i = 0; i < chance.size(); i++) {
				Arrow arrow = chance.get(i);
				Cursor newcursor = cursor.move(arrow);
				//System.out.println("----flag:" + flag);
				//ArrayList<Point> newFinished = (ArrayList<Point>) finished.clone();
				//ArrayList<ArrayList<Boolean>> newflag = (ArrayList<ArrayList<Boolean>>) flag.clone();
				//System.out.println(flag);
				if(this.valid(newcursor, counter, finished, flag)) {
					ArrayList<ArrayList<Boolean>> newflag = (ArrayList<ArrayList<Boolean>>) this.cloneArrayList(flag);
					ArrayList<Point> newFinished = (ArrayList<Point>) this.cloneArrayList(finished);
					newflag.get(newcursor.getY()).set(newcursor.getX(), true);
					newFinished.add(newcursor.toPoint());
					this.recur(newcursor, counter, newFinished, newflag);
				}
			}
		}
		//ArrayList<Integer> currentCounter = this.counter.get(counter.);
	}

	/*public boolean recur(Cursor cursor, int counter) {
		boolean hasChanged = false;
		int x = cursor.getX();
		int y = cursor.getY();
		ArrayList<Point> finished = new ArrayList<Point>();
		ArrayList<Arrow> chance = this.chance.get(y).get(x);
		ArrayList<Integer> currentCounter = this.counter.get(counter);
		for (int i = 0; i < chance.size(); i++) {
			Arrow arrow = chance.get(i);
			cursor.move(arrow);
			if(!this.valid(cursor)) {
				cursor.unmove(arrow);
			} else {
				currentCounter.add(x + y * this.width);
				hasChanged = this.recur(cursor, counter, finished);
			}
		}
		return hasChanged;
	}*/
	/*public boolean recur(Cursor cursor, int counter, ArrayList<Point> finished) {
		if(finished.size() < this.height * this.width) {
			boolean hasChanged = false;
			int x = cursor.getX();
			int y = cursor.getY();
			ArrayList<Arrow> chance = this.chance.get(y).get(x);
			ArrayList<Integer> currentCounter = this.counter.get(counter);
			for (int i = 0; i < chance.size(); i++) {
				Arrow arrow = chance.get(i);
				if(!this.valid(cursor.move(arrow))) {
					cursor.unmove(arrow);
				} else {
					hasChanged = true;
					currentCounter.add(x + y * this.width);
					finished.add(cursor.toPoint());
					this.recur(cursor, counter, finished);
				}
			}
			return hasChanged;
		} else {
			return false;
		}
	}*/
	public ArrayList cloneArrayList(ArrayList arr) {
		ArrayList newArr = new ArrayList();
		for (Object object : arr) {
			if(object.getClass().getName() == "java.util.ArrayList" ) {
				newArr.add(this.cloneArrayList((ArrayList) object));
			} else if(object.getClass().getName() == "cn.laysoft.gocheck.Point") {
				Point p = (Point) object;
				newArr.add(new Point(p.getX(), p.getY()));
			} else {
				newArr.add(object);
			}
		}
		return newArr;
	}
	
	public boolean valid(Point point, Counter counter, ArrayList<Point> finished, ArrayList<ArrayList<Boolean>> flag) {
		int x = point.getX();
		int y = point.getY();
		//System.out.println(flag);
		//System.out.println(finished);
		if(x >= this.width || x < 0) {
			return false;
		}
		if(y >= this.height || y < 0) {
			return false;
		}
		if(flag.get(y).get(x)) {
			return false;
		}
		System.out.println("--------isTrue:---------");
		return true;
	}
}

class Result {
	private ArrayList<Integer> result = new ArrayList<Integer>();
	
	public Result() {
	}
	public void add(int value) {
		this.result.add(value);
	}
}
enum Arrow {
	UP_LEFT(-1, -1), UP_CENTER(0, -1), UP_RIGHT(1, -1), MIDDLE_LEFT(-1, 0), MIDDLE_RIGHT(1, 0), BOTTOM_LEFT(-1, 1), BOTTOM_CENTER(0, 1), BOTTOM_RIGHT(1, 1);
	private int x;
	private int y;
	public int getX() {
		return x;
	}
	public int getY() {
		return y;
	}
	// 构造方法
    private Arrow(int x, int y) {
        this.x = x;
        this.y = y;
    }
    public String toString() {
		return this.x + ", " + this.y;
    }
}
class Counter {
	private int num = 0;
	public Counter() {
	}
	public void increase() {
		this.num++;
	}
	public void decrease() {
		this.num--;
	}
	public int get() {
		return this.num;
	}
}
class Cursor extends Point {
	public Cursor(int x, int y) {
		super(x, y);
	}
	public Cursor move(Arrow arrow) {
		//this.x += arrow.getX();
		//this.y += arrow.getY();
		return new Cursor(this.x + arrow.getX(), this.y + arrow.getY());
	}
	public Cursor unmove(Arrow arrow) {
		this.x -= arrow.getX();
		this.y -= arrow.getY();
		return this;
	}
	public Point toPoint() {
		return new Point(this.x, this.y);
	}
	public Cursor toCursor() {
		return new Cursor(this.x, this.y);
	}
}
class Point {
	protected int x;
	protected int y;
	public boolean equals(Point obj) {
		if(this.x == obj.x && this.y == obj.y) {
			return true;
		}
		return false;
	}
	public Point(int x, int y) {
		this.x = x;
		this.y = y;
	}
	public int getX() {
		return x;
	}
	public int getY() {
		return y;
	}
	public void setX(int x) {
		this.x = x;
	}
	public void setY(int y) {
		this.y = y;
	}
	public void setXY(int x, int y) {
		this.x = x;
		this.y = y;
	}
	public int sum() {
		return this.x + this.y;
	}
	public String toString() {
		return this.x + "," + this.y;
	}
}


